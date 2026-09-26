<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Payments\CheckoutSession;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;
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
    public function __construct(private readonly PaymentGateway $gateway) {}

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
     * Abandon a pending order: expire its Stripe session so it can no longer be
     * paid, then mark it failed. When Stripe refuses (most likely the customer
     * has just paid, or the session already closed), the order stays pending
     * and the webhook settles it. Returns true when the order is now failed.
     */
    public function abandon(Order $order): bool
    {
        if (! $order->isPending()) {
            return false;
        }

        if ($order->stripe_checkout_session_id !== null) {
            try {
                $this->gateway->expireCheckoutSession($order->stripe_checkout_session_id);
            } catch (PaymentGatewayException $e) {
                Log::info('Could not expire checkout session; leaving the order pending', [
                    'order_id' => $order->getKey(),
                    'reason' => $e->getMessage(),
                ]);

                return false;
            }
        }

        $updated = Order::query()
            ->whereKey($order->getKey())
            ->where('status', OrderStatus::Pending->value)
            ->update(['status' => OrderStatus::Failed->value, 'updated_at' => now()]);

        $order->refresh();

        return $updated === 1;
    }

    /**
     * Abandon every pending order of the user that has an open Stripe session,
     * so an earlier checkout (another tab, the back button) can't be paid on
     * top of a new one. Returns how many orders were marked failed.
     */
    public function abandonPendingOrders(User $user): int
    {
        return $user->orders()
            ->where('status', OrderStatus::Pending->value)
            ->whereNotNull('stripe_checkout_session_id')
            ->get()
            ->filter(fn (Order $order) => $this->abandon($order))
            ->count();
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
     * Returns how many books left the customer's library, or null when the
     * order was not paid (already refunded, pending, failed): idempotent.
     */
    public function refund(Order $order): ?int
    {
        $removed = DB::transaction(function () use ($order) {
            $updated = Order::query()
                ->whereKey($order->getKey())
                ->where('status', OrderStatus::Paid->value)
                ->update(['status' => OrderStatus::Refunded->value, 'updated_at' => now()]);

            if ($updated !== 1) {
                return null;
            }

            return $this->revokeLibraryRows($order);
        });

        $order->refresh();

        return $removed;
    }

    /**
     * Remove the library rows this order granted. A book the customer also
     * bought in another *paid* order stays in the library, re-pointed at that
     * order, so refunding a duplicate purchase never takes away a paid book.
     * Returns the number of rows deleted.
     */
    private function revokeLibraryRows(Order $order): int
    {
        $deleted = 0;

        $rows = DB::table('book_user')->where('order_id', $order->getKey())->get(['id', 'user_id', 'book_id']);

        foreach ($rows as $row) {
            $otherOrderId = OrderItem::query()
                ->where('book_id', $row->book_id)
                ->where('order_id', '!=', $order->getKey())
                ->whereHas('order', fn ($query) => $query
                    ->where('user_id', $row->user_id)
                    ->where('status', OrderStatus::Paid->value))
                ->orderBy('order_id')
                ->value('order_id');

            if ($otherOrderId !== null) {
                DB::table('book_user')->where('id', $row->id)->update(['order_id' => $otherOrderId, 'updated_at' => now()]);
            } else {
                $deleted += DB::table('book_user')->where('id', $row->id)->delete();
            }
        }

        return $deleted;
    }

    /**
     * Handle a Stripe `charge.dispute.closed` payload: a lost chargeback is
     * treated like a full refund (access revoked).
     *
     * @param  array<string, mixed>  $dispute
     */
    public function revokeForLostDispute(array $dispute): ?Order
    {
        if (($dispute['status'] ?? null) !== 'lost') {
            return null;
        }

        $order = $this->findOrderByPaymentIntent($dispute['payment_intent'] ?? null);

        if ($order === null) {
            return null;
        }

        Log::warning('Chargeback lost; revoking access', ['order_id' => $order->getKey()]);
        $this->refund($order);

        return $order;
    }

    /**
     * Handle a Stripe `charge.refunded` payload. Only full refunds revoke access.
     *
     * @param  array<string, mixed>  $charge
     */
    public function refundFromCharge(array $charge): ?Order
    {
        $order = $this->findOrderByPaymentIntent($charge['payment_intent'] ?? null);

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

    /**
     * @param  string|array<string, mixed>|null  $paymentIntent  id or expanded object
     */
    private function findOrderByPaymentIntent(string|array|null $paymentIntent): ?Order
    {
        $paymentIntent = is_array($paymentIntent) ? ($paymentIntent['id'] ?? null) : $paymentIntent;

        if (! is_string($paymentIntent) || $paymentIntent === '') {
            return null;
        }

        return Order::query()->where('stripe_payment_intent_id', $paymentIntent)->first();
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
