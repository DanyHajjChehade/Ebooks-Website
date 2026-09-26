<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    /**
     * @param  StripeClient|null  $stripe  null when STRIPE_SECRET is not configured
     */
    public function __construct(private readonly ?StripeClient $stripe) {}

    private function client(): StripeClient
    {
        return $this->stripe ?? throw new PaymentGatewayException('Stripe is not configured: set STRIPE_SECRET.');
    }

    public function createCheckoutSession(Order $order, User $customer, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $order->loadMissing('items');

        // Free items in a paid order are granted on fulfilment but not sent to
        // Stripe (a $0 line item adds nothing to the charge).
        $lineItems = $order->items
            ->filter(fn (OrderItem $item) => $item->price_cents > 0)
            ->map(fn (OrderItem $item) => [
                'quantity' => 1,
                'price_data' => [
                    'currency' => $order->currency,
                    'unit_amount' => $item->price_cents,
                    'product_data' => [
                        'name' => $item->title,
                        'metadata' => ['book_id' => (string) $item->book_id],
                    ],
                ],
            ])
            ->values()
            ->all();

        try {
            $session = $this->client()->checkout->sessions->create([
                'mode' => 'payment',
                'customer_email' => $customer->email,
                'client_reference_id' => (string) $customer->getKey(),
                'line_items' => $lineItems,
                'metadata' => ['order_id' => (string) $order->getKey()],
                'payment_intent_data' => [
                    'metadata' => ['order_id' => (string) $order->getKey()],
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                // Stripe's minimum lifetime is 30 minutes; one extra minute keeps us clear
                // of the boundary if the request takes a moment to reach Stripe.
                'expires_at' => now()->addMinutes(31)->getTimestamp(),
                ...(config('services.stripe.automatic_tax') ? ['automatic_tax' => ['enabled' => true]] : []),
            ], [
                // Order id + creation time: stable for retries of this order, unique
                // across databases that reuse ids (e.g. after migrate:fresh in dev).
                'idempotency_key' => 'bookplanet-order-'.$order->getKey().'-'.$order->created_at?->getTimestamp(),
            ]);
        } catch (ApiErrorException $e) {
            throw new PaymentGatewayException('Stripe could not create a checkout session: '.$e->getMessage(), 0, $e);
        }

        return CheckoutSession::fromStripe($session->toArray());
    }

    public function retrieveCheckoutSession(string $sessionId): CheckoutSession
    {
        try {
            $session = $this->client()->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException $e) {
            throw new PaymentGatewayException('Stripe could not retrieve checkout session: '.$e->getMessage(), 0, $e);
        }

        return CheckoutSession::fromStripe($session->toArray());
    }

    public function expireCheckoutSession(string $sessionId): void
    {
        try {
            $this->client()->checkout->sessions->expire($sessionId);
        } catch (ApiErrorException $e) {
            throw new PaymentGatewayException($e->getMessage(), 0, $e);
        }
    }

    public function refund(Order $order): void
    {
        if (blank($order->stripe_payment_intent_id)) {
            throw new PaymentGatewayException('This order has no Stripe payment to refund.');
        }

        try {
            $this->client()->refunds->create([
                'payment_intent' => $order->stripe_payment_intent_id,
                'metadata' => ['order_id' => (string) $order->getKey()],
            ], [
                'idempotency_key' => 'bookplanet-refund-'.$order->getKey().'-'.$order->created_at?->getTimestamp(),
            ]);
        } catch (ApiErrorException $e) {
            // Stripe's own message (e.g. "Charge ch_… has already been refunded.") is shown to the admin.
            throw new PaymentGatewayException($e->getMessage(), 0, $e);
        }
    }
}
