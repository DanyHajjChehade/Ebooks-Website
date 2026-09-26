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
}
