<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutCancelRequest;
use App\Http\Requests\CheckoutSuccessRequest;
use App\Models\Order;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;
use App\Rules\StripeChargeableAmount;
use App\Services\Cart;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly Cart $cart,
        private readonly CheckoutService $checkout,
        private readonly PaymentGateway $gateway,
    ) {}

    /**
     * Turn the cart into a pending order priced from the DB and hand the
     * customer to Stripe Checkout. Free carts are fulfilled immediately.
     */
    public function store(Request $request): RedirectResponse
    {
        $prunedMessage = $this->prunedMessage($this->cart->prune());
        $books = $this->cart->items();

        if ($books->isEmpty()) {
            return redirect()->route('cart.index')->with(
                $prunedMessage ? 'status' : 'error',
                $prunedMessage ?? 'Your cart is empty.',
            );
        }

        $subtotal = $this->cart->subtotalCents();

        // Admin validation keeps prices at 0 or >= 50 cents; guard anyway (Stripe's minimum charge).
        if ($subtotal > 0 && $subtotal < StripeChargeableAmount::MINIMUM_CENTS) {
            return redirect()->route('cart.index')
                ->with('error', 'Card payments must be at least 0.50. Add another book to check out.');
        }

        $user = $request->user();

        // Close any earlier checkout that is still open (another tab, the back
        // button), so the customer can't pay twice for the same books.
        $this->checkout->abandonPendingOrders($user);

        $order = $this->checkout->createOrder($user, $books);

        if ($order->subtotal_cents === 0) {
            $this->checkout->fulfil($order);
            $this->cart->removeMany($order->items->pluck('book_id'));

            return $this->withPrunedMessage(
                redirect()->route('checkout.success', ['order' => $order->getKey()]),
                $prunedMessage,
            );
        }

        try {
            $session = $this->gateway->createCheckoutSession(
                $order,
                $user,
                // {CHECKOUT_SESSION_ID} is substituted by Stripe; it must not be URL-encoded.
                route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                // The order id is a fallback in case the placeholder is not substituted.
                route('checkout.cancel', ['order' => $order->getKey()]).'&session_id={CHECKOUT_SESSION_ID}',
            );
        } catch (PaymentGatewayException $e) {
            report($e);
            $order->update(['status' => OrderStatus::Failed]);

            return redirect()->route('cart.index')
                ->with('error', 'We could not start the payment. Please try again in a moment.');
        }

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $this->withPrunedMessage(redirect()->away((string) $session->url, 303), $prunedMessage);
    }

    /**
     * Stripe redirects here after payment. Access is granted only once the
     * session is verified server-side (the webhook does the same, idempotently).
     */
    public function success(CheckoutSuccessRequest $request): View
    {
        $user = $request->user();

        if ($sessionId = $request->validated('session_id')) {
            $order = Order::query()
                ->where('stripe_checkout_session_id', $sessionId)
                ->whereBelongsTo($user)
                ->first();

            abort_if($order === null, 404);

            if ($order->isPending()) {
                try {
                    $this->checkout->confirmCheckoutSession($this->gateway->retrieveCheckoutSession($sessionId));
                } catch (PaymentGatewayException $e) {
                    // The webhook will still fulfil the order; the page shows "processing".
                    report($e);
                }

                $order->refresh();
            }
        } else {
            $order = Order::query()->findOrFail($request->validated('order'));
            $this->authorize('view', $order);
        }

        $order->load('items.book.author');

        if ($order->isPaid()) {
            $this->cart->removeMany($order->items->pluck('book_id'));
        }

        return view('checkout.success', ['order' => $order]);
    }

    /**
     * The customer left Stripe's page. Expire that session and mark the order
     * failed (unless Stripe says it can't be expired, e.g. it was just paid).
     */
    public function cancel(CheckoutCancelRequest $request): RedirectResponse
    {
        $user = $request->user();
        $sessionId = $request->validated('session_id');
        $orderId = $request->validated('order');
        $order = null;

        // Always scoped to the signed-in customer's own orders.
        if (is_string($sessionId) && str_starts_with($sessionId, 'cs_')) {
            $order = $user->orders()->where('stripe_checkout_session_id', $sessionId)->first();
        }

        if ($order === null && $orderId !== null) {
            $order = $user->orders()->whereKey($orderId)->first();
        }

        if ($order !== null) {
            $this->checkout->abandon($order);
        }

        return redirect()->route('cart.index')
            ->with('status', 'Checkout cancelled. Your books are still in your cart.');
    }

    /**
     * "We took out 1 book you already own." (DESIGN.md §2).
     *
     * @param  array{owned: int, unavailable: int}  $pruned
     */
    private function prunedMessage(array $pruned): ?string
    {
        $total = $pruned['owned'] + $pruned['unavailable'];

        if ($total === 0) {
            return null;
        }

        $books = $total === 1 ? '1 book' : "{$total} books";

        return match (true) {
            $pruned['unavailable'] === 0 => "We took out {$books} you already own.",
            $pruned['owned'] === 0 => "We took out {$books} that ".($total === 1 ? 'is' : 'are').' no longer for sale.',
            default => "We took out {$books} you already own or that are no longer for sale.",
        };
    }

    private function withPrunedMessage(RedirectResponse $response, ?string $message): RedirectResponse
    {
        return $message === null ? $response : $response->with('status', $message);
    }
}
