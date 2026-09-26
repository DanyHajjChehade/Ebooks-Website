<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
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
        $books = $this->cart->items();

        if ($books->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cart->subtotalCents();

        // Admin validation keeps prices at 0 or >= 50 cents; guard anyway (Stripe's minimum charge).
        if ($subtotal > 0 && $subtotal < StripeChargeableAmount::MINIMUM_CENTS) {
            return redirect()->route('cart.index')
                ->with('error', 'Card payments must be at least 0.50. Add another book to check out.');
        }

        $user = $request->user();
        $order = $this->checkout->createOrder($user, $books);

        if ($order->subtotal_cents === 0) {
            $this->checkout->fulfil($order);
            $this->cart->removeMany($order->items->pluck('book_id'));

            return redirect()->route('checkout.success', ['order' => $order->getKey()]);
        }

        try {
            $session = $this->gateway->createCheckoutSession(
                $order,
                $user,
                // {CHECKOUT_SESSION_ID} is substituted by Stripe; it must not be URL-encoded.
                route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                route('checkout.cancel'),
            );
        } catch (PaymentGatewayException $e) {
            report($e);
            $order->update(['status' => OrderStatus::Failed]);

            return redirect()->route('cart.index')
                ->with('error', 'We could not start the payment. Please try again in a moment.');
        }

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return redirect()->away((string) $session->url, 303);
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

    public function cancel(): RedirectResponse
    {
        return redirect()->route('cart.index')
            ->with('status', 'Checkout cancelled — your cart is saved.');
    }
}
