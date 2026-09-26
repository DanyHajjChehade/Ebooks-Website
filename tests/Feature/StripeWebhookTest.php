<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->price(1500)->create();
        $this->order = app(CheckoutService::class)->createOrder($this->user, collect([$this->book]));
        $this->order->update(['stripe_checkout_session_id' => 'cs_test_webhook']);
    }

    public function test_a_valid_completed_session_fulfils_the_order(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order))
            ->assertOk()
            ->assertJson(['received' => true]);

        $this->order->refresh();
        $this->assertSame(OrderStatus::Paid, $this->order->status);
        $this->assertSame('pi_test_'.$this->order->id, $this->order->stripe_payment_intent_id);
        $this->assertTrue($this->user->ownsBook($this->book));
    }

    public function test_an_invalid_signature_is_rejected(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order), secret: 'whsec_wrong')
            ->assertStatus(400);

        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);
        $this->assertFalse($this->user->ownsBook($this->book));
    }

    public function test_a_missing_signature_is_rejected(): void
    {
        $this->postJson('/stripe/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => $this->stripeSessionPayload($this->order)],
        ])->assertStatus(400);

        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);
    }

    public function test_a_stale_signature_is_rejected(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order), timestamp: time() - 3600)
            ->assertStatus(400);

        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);
    }

    public function test_webhook_fails_closed_without_a_configured_secret(): void
    {
        $payload = $this->stripeSessionPayload($this->order);
        config(['services.stripe.webhook_secret' => null]);

        $this->postStripeEvent('checkout.session.completed', $payload, secret: 'anything')->assertStatus(500);

        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);
    }

    public function test_async_payments_are_fulfilled_when_they_succeed(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, ['payment_status' => 'unpaid']))->assertOk();
        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);

        $this->postStripeEvent('checkout.session.async_payment_succeeded', $this->stripeSessionPayload($this->order))->assertOk();
        $this->assertSame(OrderStatus::Paid, $this->order->refresh()->status);
        $this->assertTrue($this->user->ownsBook($this->book));
    }

    public function test_expired_and_failed_sessions_mark_the_order_failed(): void
    {
        $this->postStripeEvent('checkout.session.expired', $this->stripeSessionPayload($this->order, ['payment_status' => 'unpaid', 'status' => 'expired']))->assertOk();
        $this->assertSame(OrderStatus::Failed, $this->order->refresh()->status);

        $other = app(CheckoutService::class)->createOrder($this->user, collect([Book::factory()->create()]));
        $other->update(['stripe_checkout_session_id' => 'cs_test_other']);

        $this->postStripeEvent('checkout.session.async_payment_failed', $this->stripeSessionPayload($other, ['payment_status' => 'unpaid']))->assertOk();
        $this->assertSame(OrderStatus::Failed, $other->refresh()->status);
    }

    public function test_expiry_never_downgrades_a_paid_order(): void
    {
        app(CheckoutService::class)->fulfil($this->order);

        $this->postStripeEvent('checkout.session.expired', $this->stripeSessionPayload($this->order, ['payment_status' => 'unpaid']))->assertOk();

        $this->assertSame(OrderStatus::Paid, $this->order->refresh()->status);
    }

    public function test_a_mismatched_amount_or_currency_is_not_fulfilled(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, ['amount_total' => 100, 'amount_subtotal' => 100]))->assertOk();
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, ['currency' => 'eur']))->assertOk();

        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);
    }

    public function test_tax_on_top_of_the_subtotal_is_accepted(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, ['amount_total' => 1800, 'amount_subtotal' => 1500]))->assertOk();

        $this->assertSame(OrderStatus::Paid, $this->order->refresh()->status);
    }

    public function test_sessions_that_do_not_match_an_order_are_ignored(): void
    {
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, [
            'id' => 'cs_test_webhook',
            'metadata' => ['order_id' => '999'],
        ]))->assertOk();

        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, [
            'id' => 'cs_unknown',
            'metadata' => ['order_id' => '999'],
        ]))->assertOk();

        $this->assertSame(OrderStatus::Pending, $this->order->refresh()->status);
    }

    public function test_session_id_is_adopted_when_the_webhook_arrives_first(): void
    {
        $this->order->update(['stripe_checkout_session_id' => null]);

        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($this->order, ['id' => 'cs_test_early']))->assertOk();

        $this->order->refresh();
        $this->assertSame('cs_test_early', $this->order->stripe_checkout_session_id);
        $this->assertSame(OrderStatus::Paid, $this->order->status);
    }

    public function test_a_full_refund_revokes_access(): void
    {
        app(CheckoutService::class)->fulfil($this->order, 'pi_refund_me');

        $this->postStripeEvent('charge.refunded', [
            'id' => 'ch_1',
            'object' => 'charge',
            'payment_intent' => 'pi_refund_me',
            'amount' => 1500,
            'amount_refunded' => 1500,
            'refunded' => true,
        ])->assertOk();

        $this->assertSame(OrderStatus::Refunded, $this->order->refresh()->status);
        $this->assertFalse($this->user->ownsBook($this->book));
    }

    public function test_a_partial_refund_keeps_access(): void
    {
        app(CheckoutService::class)->fulfil($this->order, 'pi_partial');

        $this->postStripeEvent('charge.refunded', [
            'id' => 'ch_2',
            'object' => 'charge',
            'payment_intent' => 'pi_partial',
            'amount' => 1500,
            'amount_refunded' => 500,
            'refunded' => false,
        ])->assertOk();

        $this->assertSame(OrderStatus::Paid, $this->order->refresh()->status);
        $this->assertTrue($this->user->ownsBook($this->book));
    }

    public function test_refunding_one_order_keeps_books_granted_by_another(): void
    {
        app(CheckoutService::class)->fulfil($this->order, 'pi_first');

        // A second (duplicate) order for the same book: the library row stays tied to the first.
        $second = app(CheckoutService::class)->createOrder($this->user, collect([$this->book]));
        app(CheckoutService::class)->fulfil($second, 'pi_second');

        $this->postStripeEvent('charge.refunded', ['object' => 'charge', 'payment_intent' => 'pi_second', 'amount' => 1500, 'amount_refunded' => 1500, 'refunded' => true])->assertOk();

        $this->assertSame(OrderStatus::Refunded, $second->refresh()->status);
        $this->assertTrue($this->user->ownsBook($this->book));
    }

    public function test_unknown_event_types_are_acknowledged(): void
    {
        $this->postStripeEvent('customer.created', ['id' => 'cus_1', 'object' => 'customer'])->assertOk();
    }
}
