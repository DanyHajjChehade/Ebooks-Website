<?php

namespace Tests;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Payments\PaymentGateway;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Tests\Fakes\FakePaymentGateway;

abstract class TestCase extends BaseTestCase
{
    protected function admin(array $attributes = []): User
    {
        return User::factory()->admin()->create($attributes);
    }

    protected function fakeGateway(): FakePaymentGateway
    {
        $fake = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $fake);

        return $fake;
    }

    /**
     * Give a user a paid order containing the given books (library access included).
     */
    protected function purchase(User $user, Book ...$books): Order
    {
        $order = app(CheckoutService::class)->createOrder($user, collect($books));
        app(CheckoutService::class)->fulfil($order, 'pi_test_'.$order->getKey());

        return $order->refresh();
    }

    /**
     * POST a Stripe event to the webhook, signed like Stripe does.
     *
     * @param  array<string, mixed>  $object
     */
    protected function postStripeEvent(string $type, array $object, ?string $secret = null, ?int $timestamp = null): TestResponse
    {
        $payload = json_encode([
            'id' => 'evt_test_'.uniqid(),
            'object' => 'event',
            'type' => $type,
            'data' => ['object' => $object],
        ], JSON_THROW_ON_ERROR);

        $timestamp ??= time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret ?? (string) config('services.stripe.webhook_secret'));

        return $this->call('POST', '/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload);
    }

    /**
     * @return array<string, mixed>
     */
    protected function stripeSessionPayload(Order $order, array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => $order->stripe_checkout_session_id,
            'object' => 'checkout.session',
            'payment_status' => 'paid',
            'status' => 'complete',
            'amount_total' => $order->subtotal_cents,
            'amount_subtotal' => $order->subtotal_cents,
            'currency' => $order->currency,
            'payment_intent' => 'pi_test_'.$order->getKey(),
            'metadata' => ['order_id' => (string) $order->getKey()],
        ], $overrides);
    }
}
