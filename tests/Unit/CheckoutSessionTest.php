<?php

namespace Tests\Unit;

use App\Payments\CheckoutSession;
use PHPUnit\Framework\TestCase;

class CheckoutSessionTest extends TestCase
{
    public function test_it_maps_a_stripe_payload(): void
    {
        $session = CheckoutSession::fromStripe([
            'id' => 'cs_1',
            'url' => 'https://checkout.stripe.com/c/pay/cs_1',
            'payment_status' => 'paid',
            'amount_total' => 1800,
            'amount_subtotal' => 1500,
            'currency' => 'USD',
            'payment_intent' => ['id' => 'pi_1'],
            'metadata' => ['order_id' => '42'],
        ]);

        $this->assertSame('cs_1', $session->id);
        $this->assertTrue($session->isPaid());
        $this->assertSame(1500, $session->goodsAmount());
        $this->assertSame('usd', $session->currency);
        $this->assertSame(42, $session->orderId);
        $this->assertSame('pi_1', $session->paymentIntentId);
    }

    public function test_missing_or_hostile_fields_are_tolerated(): void
    {
        $session = CheckoutSession::fromStripe(['id' => 'cs_2', 'metadata' => ['order_id' => '1 OR 1=1']]);

        $this->assertFalse($session->isPaid());
        $this->assertNull($session->orderId);
        $this->assertNull($session->goodsAmount());
    }
}
