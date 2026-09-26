<?php

namespace App\Payments;

/**
 * Provider-agnostic snapshot of a hosted checkout session.
 */
final readonly class CheckoutSession
{
    public function __construct(
        public string $id,
        public ?string $url,
        public string $paymentStatus,
        public ?int $amountTotal,
        public ?string $currency,
        public ?int $orderId,
        public ?string $paymentIntentId = null,
        public ?int $amountSubtotal = null,
    ) {}

    /**
     * Build from a Stripe Checkout Session payload (API response or webhook object).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromStripe(array $data): self
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        $paymentIntent = $data['payment_intent'] ?? null;

        if (is_array($paymentIntent)) {
            $paymentIntent = $paymentIntent['id'] ?? null;
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            url: $data['url'] ?? null,
            paymentStatus: (string) ($data['payment_status'] ?? 'unpaid'),
            amountTotal: isset($data['amount_total']) ? (int) $data['amount_total'] : null,
            currency: isset($data['currency']) ? strtolower((string) $data['currency']) : null,
            orderId: is_numeric($orderId) ? (int) $orderId : null,
            paymentIntentId: is_string($paymentIntent) ? $paymentIntent : null,
            amountSubtotal: isset($data['amount_subtotal']) ? (int) $data['amount_subtotal'] : null,
        );
    }

    /**
     * Amount of the goods before tax. With Stripe Tax enabled the total also
     * includes tax, so this is what must match our order subtotal.
     */
    public function goodsAmount(): ?int
    {
        return $this->amountSubtotal ?? $this->amountTotal;
    }

    public function isPaid(): bool
    {
        // "no_payment_required" covers 100%-discounted sessions.
        return in_array($this->paymentStatus, ['paid', 'no_payment_required'], true);
    }
}
