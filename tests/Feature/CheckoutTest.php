<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\Cart;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->fakeGateway();
    }

    public function test_guests_must_log_in_to_check_out(): void
    {
        $this->post('/checkout')->assertRedirect('/login');
        $this->get('/checkout/success?session_id=x')->assertRedirect('/login');
    }

    public function test_an_empty_cart_cannot_be_checked_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame([], $this->gateway->created);
    }

    public function test_checkout_creates_a_pending_order_priced_from_the_database_and_redirects_to_stripe(): void
    {
        $user = User::factory()->create();
        $a = Book::factory()->price(1500)->create();
        $b = Book::factory()->price(2000, 1200)->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $a->id]);
        $this->post('/cart', ['book_id' => $b->id]);

        // The price changes after the book went into the cart: the DB price wins.
        $a->update(['price_cents' => 1700]);

        $response = $this->post('/checkout');

        $order = Order::sole();
        $response->assertStatus(303)->assertRedirect('https://checkout.stripe.test/pay/'.$order->stripe_checkout_session_id);

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertTrue($order->user->is($user));
        $this->assertSame(2900, $order->subtotal_cents);
        $this->assertEqualsCanonicalizing([1700, 1200], $order->items->pluck('price_cents')->all());
        $this->assertSame($b->title, $order->items->firstWhere('book_id', $b->id)->title);
        $this->assertStringEndsWith('?session_id={CHECKOUT_SESSION_ID}', $this->gateway->created[0]['success']);
        $this->assertDatabaseCount('book_user', 0);
    }

    public function test_client_supplied_prices_and_users_are_ignored(): void
    {
        $user = User::factory()->create();
        $victim = User::factory()->create();
        $book = Book::factory()->price(1500)->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id, 'price' => 1]);
        $this->post('/checkout', ['subtotal_cents' => 1, 'user_id' => $victim->id, 'price_cents' => 1]);

        $order = Order::sole();
        $this->assertSame(1500, $order->subtotal_cents);
        $this->assertSame($user->id, $order->user_id);
    }

    public function test_successful_payment_fulfils_the_order_and_clears_the_cart(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id]);
        $this->post('/checkout');
        $sessionId = $this->gateway->lastSessionId();
        $this->gateway->markPaid($sessionId, paymentIntent: 'pi_123');

        $this->get('/checkout/success?session_id='.$sessionId)
            ->assertOk()
            ->assertViewIs('checkout.success')
            ->assertViewHas('order', fn (Order $o) => $o->isPaid());

        $order = Order::sole();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('pi_123', $order->stripe_payment_intent_id);
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($user->ownsBook($book));
        $this->assertSame([], session(Cart::SESSION_KEY));
    }

    public function test_an_unpaid_session_does_not_grant_access(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id]);
        $this->post('/checkout');

        $this->get('/checkout/success?session_id='.$this->gateway->lastSessionId())
            ->assertOk()
            ->assertViewHas('order', fn (Order $o) => $o->isPending());

        $this->assertFalse($user->ownsBook($book));
        $this->assertSame([$book->id], session(Cart::SESSION_KEY));
    }

    public function test_a_session_with_the_wrong_amount_is_not_fulfilled(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->price(1500)->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id]);
        $this->post('/checkout');
        $sessionId = $this->gateway->lastSessionId();
        $this->gateway->markPaid($sessionId, amount: 100);

        $this->get('/checkout/success?session_id='.$sessionId)->assertOk();

        $this->assertSame(OrderStatus::Pending, Order::sole()->status);
        $this->assertFalse($user->ownsBook($book));
    }

    public function test_another_users_session_cannot_be_claimed(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($owner)->post('/cart', ['book_id' => $book->id]);
        $this->post('/checkout');
        $sessionId = $this->gateway->lastSessionId();
        $this->gateway->markPaid($sessionId);

        $attacker = User::factory()->create();
        $this->actingAs($attacker)->get('/checkout/success?session_id='.$sessionId)->assertNotFound();
        $this->actingAs($attacker)->get('/checkout/success?session_id=cs_unknown')->assertNotFound();
        $this->actingAs($attacker)->get('/checkout/success?order='.Order::sole()->id)->assertForbidden();

        $this->assertFalse($attacker->ownsBook($book));
        $this->assertSame(OrderStatus::Pending, Order::sole()->status);
    }

    public function test_success_page_requires_a_session_or_order(): void
    {
        $this->actingAs(User::factory()->create())->get('/checkout/success')->assertSessionHasErrors('session_id');
    }

    public function test_fulfilment_is_idempotent(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(2)->create();
        $checkout = app(CheckoutService::class);
        $order = $checkout->createOrder($user, $books);

        $this->assertTrue($checkout->fulfil($order, 'pi_1'));
        $paidAt = $order->paid_at;

        $this->travel(5)->minutes();
        $this->assertFalse($checkout->fulfil($order, 'pi_2'));
        $this->assertFalse($checkout->fulfil(Order::find($order->id)));

        $order->refresh();
        $this->assertSame('pi_1', $order->stripe_payment_intent_id);
        $this->assertTrue($order->paid_at->equalTo($paidAt));
        $this->assertDatabaseCount('book_user', 2);
    }

    public function test_success_page_and_webhook_racing_grant_access_once(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id]);
        $this->post('/checkout');
        $sessionId = $this->gateway->lastSessionId();
        $this->gateway->markPaid($sessionId);
        $order = Order::sole();

        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($order))->assertOk();
        $this->actingAs($user)->get('/checkout/success?session_id='.$sessionId)->assertOk();
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($order))->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
        $this->assertDatabaseCount('book_user', 1);
    }

    public function test_a_free_cart_is_fulfilled_without_stripe(): void
    {
        $user = User::factory()->create();
        $free = Book::factory()->free()->create();
        $alsoFree = Book::factory()->price(1000, 0)->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $free->id]);
        $this->post('/cart', ['book_id' => $alsoFree->id]);

        $response = $this->post('/checkout');
        $order = Order::sole();
        $response->assertRedirect(route('checkout.success', ['order' => $order->id]));

        $this->assertSame([], $this->gateway->created);
        $this->assertTrue($order->refresh()->isPaid());
        $this->assertSame(0, $order->subtotal_cents);
        $this->assertTrue($user->ownsBook($free));
        $this->assertTrue($user->ownsBook($alsoFree));

        $this->get(route('checkout.success', ['order' => $order->id]))->assertOk()->assertViewHas('order', fn ($o) => $o->is($order));
    }

    public function test_owned_books_are_dropped_at_checkout(): void
    {
        $user = User::factory()->create();
        [$owned, $wanted] = Book::factory()->count(2)->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $owned->id]);
        $this->post('/cart', ['book_id' => $wanted->id]);
        $this->purchase($user, $owned); // e.g. bought on another device

        $this->post('/checkout')->assertStatus(303);

        $order = Order::query()->where('status', 'pending')->sole();
        $this->assertSame([$wanted->id], $order->items->pluck('book_id')->all());
        $this->assertSame($wanted->price_cents, $order->subtotal_cents);
    }

    public function test_a_total_below_the_stripe_minimum_is_rejected(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->price(30)->create(); // bypasses admin validation on purpose

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id]);

        $this->post('/checkout')->assertRedirect(route('cart.index'))->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_gateway_failures_mark_the_order_failed(): void
    {
        $this->gateway->failCreate = true;
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id]);

        $this->post('/checkout')->assertRedirect(route('cart.index'))->assertSessionHas('error');
        $this->assertSame(OrderStatus::Failed, Order::sole()->status);
    }

    public function test_checkout_is_rate_limited(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 10; $i++) {
            $this->post('/checkout')->assertRedirect();
        }

        $this->post('/checkout')->assertTooManyRequests();
    }

    public function test_cancel_returns_to_the_cart(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/checkout/cancel')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Checkout cancelled. Your books are still in your cart.');
    }
}
