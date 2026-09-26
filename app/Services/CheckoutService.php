<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Payments\CheckoutSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Creates orders from the cart and turns verified payments into library
 * access. Every state transition is a conditional UPDATE inside a
 * transaction, and library rows are protected by unique(user_id, book_id),
 * so the success page and the webhook can race safely.
 */
class CheckoutService
{
    /**
     * Create a pending order for the given books using current DB prices.
     *
     * @param  Collection<int, Book>  $books
     */
    public function createOrder(User $user, Collection $books): Order
    {
        return DB::transaction(function () use ($user, $books) {
            /** @var Order $order */
            $order = $user->orders()->create([
                'status' => OrderStatus::Pending,
                'subtotal_cents' => (int) $books->sum(fn (Book $book) => $book->effective_price_cents),
                'currency' => config('services.stripe.currency', 'usd'),
            ]);

            $order->items()->createMany($books->map(fn (Book $book) => [
                'book_id' => $book->getKey(),
                'title' => $book->title,
                'price_cents' => $book->effective_price_cents,
            ])->all());

            return $order->load('items');
        });
    }

    /**
     * Mark the order paid and grant its books. Idempotent: returns false when
     * the order was already fulfilled (or refunded) by a concurrent request.
     */
    public function fulfil(Order $order, ?string $paymentIntentId = null): bool
    {
        $fulfilled = DB::transaction(function () use ($order, $paymentIntentId) {
            // Row lock on MySQL/Postgres; SQLite serialises writers anyway.
            $locked = Order::query()->lockForUpdate()->find($order->getKey());

            if ($locked === null || ! in_array($locked->status, [OrderStatus::Pending, OrderStatus::Failed], true)) {
                return false;
            }

            $paidAt = now();

            // Compare-and-set: only one caller can move the order out of pending/failed.
            $updated = Order::query()
                ->whereKey($locked->getKey())
                ->whereIn('status', [OrderStatus::Pending->value, OrderStatus::Failed->value])
                ->update([
                    'status' => OrderStatus::Paid->value,
                    'paid_at' => $paidAt,
                    'stripe_payment_intent_id' => $paymentIntentId ?? $locked->stripe_payment_intent_id,
                    'updated_at' => $paidAt,
                ]);

            if ($updated !== 1) {
                return false;
            }

            if ($locked->user_id !== null) {
                $rows = $locked->items()
                    ->whereNotNull('book_id')
                    ->pluck('book_id')
                    ->map(fn ($bookId) => [
                        'user_id' => $locked->user_id,
                        'book_id' => $bookId,
                        'order_id' => $locked->getKey(),
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ])->all();

                // unique(user_id, book_id) makes a duplicate grant impossible.
                DB::table('book_user')->insertOrIgnore($rows);
            }

            return true;
        });

        $order->refresh();

        if ($fulfilled) {
            Log::info('Order fulfilled', ['order_id' => $order->getKey()]);
        }

        return $fulfilled;
    }

    /**
     * Verify a checkout session reported by Stripe (success redirect or
     * webhook) against our order, and fulfil it when it is paid.
     *
     * Returns the matching order, or null when the session does not match
     * any order we created.
     */
    public function confirmCheckoutSession(CheckoutSession $session): ?Order
    {
        $order = $this->findOrderForSession($session);

        if ($order === null) {
            Log::warning('Checkout session does not match an order', ['session' => $session->id]);

            return null;
        }

        if (! $session->isPaid()) {
            return $order;
        }

        if ($session->goodsAmount() !== $order->subtotal_cents
            || ($session->currency !== null && $session->currency !== $order->currency)) {
            Log::error('Checkout session amount mismatch; not fulfilling', [
                'order_id' => $order->getKey(),
                'session' => $session->id,
                'expected' => $order->subtotal_cents.' '.$order->currency,
                'received' => $session->goodsAmount().' '.$session->currency,
            ]);

            return $order;
        }

        $this->fulfil($order, $session->paymentIntentId);

        return $order->refresh();
    }

    /**
     * Stripe reported the session expired or its async payment failed.
     */
    public function failCheckoutSession(CheckoutSession $session): ?Order
    {
        $order = $this->findOrderForSession($session);

        if ($order !== null) {
            Order::query()
                ->whereKey($order->getKey())
                ->where('status', OrderStatus::Pending->value)
                ->update(['status' => OrderStatus::Failed->value, 'updated_at' => now()]);

            $order->refresh();
        }

        return $order;
    }

    /**
     * Mark a paid order refunded and revoke the library access it granted.
     */
    public function refund(Order $order): bool
    {
        $refunded = DB::transaction(function () use ($order) {
            $updated = Order::query()
                ->whereKey($order->getKey())
                ->where('status', OrderStatus::Paid->value)
                ->update(['status' => OrderStatus::Refunded->value, 'updated_at' => now()]);

            if ($updated !== 1) {
                return false;
            }

            DB::table('book_user')->where('order_id', $order->getKey())->delete();

            return true;
        });

        $order->refresh();

        return $refunded;
    }

    /**
     * Handle a Stripe `charge.refunded` payload. Only full refunds revoke access.
     *
     * @param  array<string, mixed>  $charge
     */
    public function refundFromCharge(array $charge): ?Order
    {
        $paymentIntent = $charge['payment_intent'] ?? null;
        $paymentIntent = is_array($paymentIntent) ? ($paymentIntent['id'] ?? null) : $paymentIntent;

        if (! is_string($paymentIntent) || $paymentIntent === '') {
            return null;
        }

        $order = Order::query()->where('stripe_payment_intent_id', $paymentIntent)->first();

        if ($order === null) {
            return null;
        }

        $amount = (int) ($charge['amount'] ?? 0);
        $refundedAmount = (int) ($charge['amount_refunded'] ?? 0);
        $fullyRefunded = ($charge['refunded'] ?? false) === true || ($amount > 0 && $refundedAmount >= $amount);

        if (! $fullyRefunded) {
            Log::info('Partial refund recorded in Stripe; access kept', ['order_id' => $order->getKey()]);

            return $order;
        }

        $this->refund($order);

        return $order;
    }

    private function findOrderForSession(CheckoutSession $session): ?Order
    {
        if ($session->id === '') {
            return null;
        }

        $order = Order::query()->where('stripe_checkout_session_id', $session->id)->first();

        if ($order !== null) {
            // Metadata, when present, must agree with the stored session id.
            return ($session->orderId === null || $session->orderId === $order->getKey()) ? $order : null;
        }

        // Fallback: the webhook arrived before we stored the session id.
        if ($session->orderId === null) {
            return null;
        }

        $order = Order::query()->find($session->orderId);

        if ($order === null || $order->stripe_checkout_session_id !== null) {
            return null;
        }

        $order->forceFill(['stripe_checkout_session_id' => $session->id])->save();

        return $order;
    }
}
