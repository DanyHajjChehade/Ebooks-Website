<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Payments\PaymentGatewayException;
use App\Payments\StripePaymentGateway;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\CurlClient;
use Stripe\StripeClient;
use Tests\Fakes\RecordingStripeHttpClient;
use Tests\TestCase;

/**
 * The real Stripe gateway, with stripe-php's HTTP layer swapped for a recorder.
 */
class StripePaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private RecordingStripeHttpClient $http;

    private StripePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = new RecordingStripeHttpClient;
        ApiRequestor::setHttpClient($this->http);
        $this->gateway = new StripePaymentGateway(new StripeClient(['api_key' => 'sk_test_123', 'max_network_retries' => 0]));
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(CurlClient::instance());

        parent::tearDown();
    }

    public function test_checkout_sessions_expire_after_thirty_minutes_and_cancel_back_to_the_order(): void
    {
        $this->freezeTime();
        $user = User::factory()->create(['email' => 'reader@example.com']);
        $order = app(CheckoutService::class)->createOrder($user, collect([
            Book::factory()->price(1500)->create(['title' => 'Paid Book']),
            Book::factory()->free()->create(),
        ]));

        $this->http->queue(['id' => 'cs_test_1', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1', 'payment_status' => 'unpaid', 'amount_total' => 1500, 'currency' => 'usd', 'metadata' => ['order_id' => (string) $order->id]]);

        $session = $this->gateway->createCheckoutSession($order, $user, 'https://shop.test/checkout/success?session_id={CHECKOUT_SESSION_ID}', 'https://shop.test/checkout/cancel?order=1&session_id={CHECKOUT_SESSION_ID}');

        $request = $this->http->last();
        $this->assertSame('post', $request['method']);
        $this->assertStringEndsWith('/v1/checkout/sessions', $request['url']);
        $this->assertSame(now()->addMinutes(31)->getTimestamp(), $request['params']['expires_at']);
        $this->assertGreaterThanOrEqual(now()->addMinutes(30)->getTimestamp(), $request['params']['expires_at']);
        $this->assertSame('https://shop.test/checkout/cancel?order=1&session_id={CHECKOUT_SESSION_ID}', $request['params']['cancel_url']);
        $this->assertSame((string) $order->id, $request['params']['metadata']['order_id']);
        $this->assertCount(1, $request['params']['line_items']); // the free item is not sent
        $this->assertSame(1500, $request['params']['line_items'][0]['price_data']['unit_amount']);
        $this->assertArrayNotHasKey('automatic_tax', $request['params']);
        $this->assertSame('cs_test_1', $session->id);
    }

    public function test_automatic_tax_is_opt_in(): void
    {
        config(['services.stripe.automatic_tax' => true]);
        $user = User::factory()->create();
        $order = app(CheckoutService::class)->createOrder($user, collect([Book::factory()->create()]));
        $this->http->queue(['id' => 'cs_test_2', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/x']);

        $this->gateway->createCheckoutSession($order, $user, 'https://shop.test/s', 'https://shop.test/c');

        // stripe-php encodes booleans as the strings Stripe's form API expects.
        $this->assertSame('true', $this->http->last()['params']['automatic_tax']['enabled']);
    }

    public function test_expiring_a_session_calls_the_expire_endpoint(): void
    {
        $this->http->queue(['id' => 'cs_test_3', 'object' => 'checkout.session', 'status' => 'expired']);

        $this->gateway->expireCheckoutSession('cs_test_3');

        $this->assertSame('post', $this->http->last()['method']);
        $this->assertStringEndsWith('/v1/checkout/sessions/cs_test_3/expire', $this->http->last()['url']);
    }

    public function test_stripe_errors_become_gateway_exceptions(): void
    {
        $this->http->queue(['error' => ['type' => 'invalid_request_error', 'message' => 'Only Checkout Sessions with a status in [open] can be expired.']], 400);

        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Only Checkout Sessions with a status in [open] can be expired.');

        $this->gateway->expireCheckoutSession('cs_test_paid');
    }
}
