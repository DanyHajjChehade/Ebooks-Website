<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\User;

/**
 * The only network-facing payment operations the store needs. Bound to
 * StripePaymentGateway in AppServiceProvider; tests swap in a fake.
 */
interface PaymentGateway
{
    /**
     * Start a hosted checkout for a pending order. The returned session must
     * carry a redirect URL.
     */
    public function createCheckoutSession(Order $order, User $customer, string $successUrl, string $cancelUrl): CheckoutSession;

    /**
     * Fetch the authoritative state of a checkout session from the provider.
     */
    public function retrieveCheckoutSession(string $sessionId): CheckoutSession;

    /**
     * Close an open checkout session so it can no longer be paid. Throws
     * PaymentGatewayException when the provider refuses, e.g. because the
     * session was already completed (paid) or has already expired.
     */
    public function expireCheckoutSession(string $sessionId): void;

    /**
     * Fully refund the payment behind a paid order. Must be safe to call
     * twice for the same order (idempotency key).
     */
    public function refund(Order $order): void;
}
