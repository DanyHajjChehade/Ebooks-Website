<?php

namespace Tests\Fakes;

use App\Models\Order;
use App\Models\User;
use App\Payments\CheckoutSession;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;

/**
 * In-memory stand-in for Stripe. Sessions are created "unpaid"; tests mark
 * them paid (or tamper with them) before hitting the success page.
 */
class FakePaymentGateway implements PaymentGateway
{
    /** @var array<string, CheckoutSession> */
    public array $sessions = [];

    /** @var list<array{order: Order, customer: User, success: string, cancel: string}> */
    public array $created = [];

    /** @var list<int> */
    public array $refunded = [];

    /** @var list<string> */
    public array $expired = [];

    public bool $failExpire = false;

    public bool $failCreate = false;

    public bool $failRefund = false;

    private int $sequence = 0;

    public function createCheckoutSession(Order $order, User $customer, string $successUrl, string $cancelUrl): CheckoutSession
    {
        if ($this->failCreate) {
            throw new PaymentGatewayException('Stripe is down');
        }

        $id = 'cs_test_fake_'.(++$this->sequence);
        $this->created[] = ['order' => $order, 'customer' => $customer, 'success' => $successUrl, 'cancel' => $cancelUrl];

        return $this->sessions[$id] = new CheckoutSession(
            id: $id,
            url: 'https://checkout.stripe.test/pay/'.$id,
            paymentStatus: 'unpaid',
            amountTotal: $order->subtotal_cents,
            currency: $order->currency,
            orderId: $order->getKey(),
            paymentIntentId: null,
            amountSubtotal: $order->subtotal_cents,
        );
    }

    public function retrieveCheckoutSession(string $sessionId): CheckoutSession
    {
        return $this->sessions[$sessionId] ?? throw new PaymentGatewayException("No such session {$sessionId}");
    }

    /**
     * Like Stripe: only open (unpaid, unexpired) sessions can be expired.
     */
    public function expireCheckoutSession(string $sessionId): void
    {
        $session = $this->sessions[$sessionId] ?? null;

        if ($this->failExpire || $session === null || $session->isPaid() || in_array($sessionId, $this->expired, true)) {
            throw new PaymentGatewayException("Session {$sessionId} is not in an expireable state.");
        }

        $this->expired[] = $sessionId;
    }

    public function refund(Order $order): void
    {
        if ($this->failRefund) {
            throw new PaymentGatewayException('Refund declined');
        }

        $this->refunded[] = $order->getKey();
    }

    /**
     * Simulate the customer completing payment on Stripe's page.
     */
    public function markPaid(string $sessionId, ?int $amount = null, string $paymentIntent = 'pi_test_fake'): CheckoutSession
    {
        $session = $this->sessions[$sessionId];

        return $this->sessions[$sessionId] = new CheckoutSession(
            id: $session->id,
            url: $session->url,
            paymentStatus: 'paid',
            amountTotal: $amount ?? $session->amountTotal,
            currency: $session->currency,
            orderId: $session->orderId,
            paymentIntentId: $paymentIntent,
            amountSubtotal: $amount ?? $session->amountSubtotal,
        );
    }

    public function lastSessionId(): ?string
    {
        return array_key_last($this->sessions);
    }
}
