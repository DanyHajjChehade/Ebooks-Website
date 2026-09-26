<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/**
 * Review finding S1: abandoned and duplicate checkouts.
 */
class AbandonedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->fakeGateway();
        $this->user = User::factory()->create();
    }

    private function startCheckout(Book ...$books): Order
    {
        foreach ($books as $book) {
            $this->actingAs($this->user)->post('/cart', ['book_id' => $book->id]);
        }

        $this->actingAs($this->user)->post('/checkout')->assertStatus(303);

        return Order::query()->latest('id')->firstOrFail();
    }

    public function test_starting_a_new_checkout_expires_the_previous_open_one(): void
    {
        $book = Book::factory()->create();

        $first = $this->startCheckout($book);
        $second = $this->startCheckout(); // back button / second tab: same cart, new checkout

        $this->assertSame([$first->stripe_checkout_session_id], $this->gateway->expired);
        $this->assertSame(OrderStatus::Failed, $first->refresh()->status);
        $this->assertSame(OrderStatus::Pending, $second->refresh()->status);
        $this->assertNotSame($first->id, $second->id);
    }

    public function test_a_session_that_cannot_be_expired_stays_pending(): void
    {
        $book = Book::factory()->create();
        $first = $this->startCheckout($book);

        // The customer paid the first session a moment ago; Stripe refuses to expire it.
        $this->gateway->markPaid($first->stripe_checkout_session_id);
        $this->startCheckout();

        $this->assertSame(OrderStatus::Pending, $first->refresh()->status);
        $this->assertSame([], $this->gateway->expired);

        // The webhook then settles it.
        $this->postStripeEvent('checkout.session.completed', $this->stripeSessionPayload($first))->assertOk();
        $this->assertSame(OrderStatus::Paid, $first->refresh()->status);
    }

    public function test_expire_errors_leave_the_order_pending(): void
    {
        $first = $this->startCheckout(Book::factory()->create());
        $this->gateway->failExpire = true;

        $this->startCheckout();

        $this->assertSame(OrderStatus::Pending, $first->refresh()->status);
    }

    public function test_other_customers_orders_are_not_touched(): void
    {
        $other = User::factory()->create();
        $theirs = app(CheckoutService::class)->createOrder($other, collect([Book::factory()->create()]));
        $theirs->update(['stripe_checkout_session_id' => 'cs_test_theirs']);

        $this->startCheckout(Book::factory()->create());

        $this->assertSame(OrderStatus::Pending, $theirs->refresh()->status);
        $this->assertNotContains('cs_test_theirs', $this->gateway->expired);
    }

    public function test_cancel_expires_that_session_and_fails_the_order(): void
    {
        $order = $this->startCheckout(Book::factory()->create());
        $cancelUrl = $this->gateway->created[0]['cancel'];

        $this->assertSame(route('checkout.cancel', ['order' => $order->id]).'&session_id={CHECKOUT_SESSION_ID}', $cancelUrl);

        $this->actingAs($this->user)
            ->get(str_replace('{CHECKOUT_SESSION_ID}', $order->stripe_checkout_session_id, $cancelUrl))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Checkout cancelled. Your books are still in your cart.');

        $this->assertSame([$order->stripe_checkout_session_id], $this->gateway->expired);
        $this->assertSame(OrderStatus::Failed, $order->refresh()->status);
    }

    public function test_cancel_falls_back_to_the_order_id_when_the_placeholder_is_not_substituted(): void
    {
        $order = $this->startCheckout(Book::factory()->create());

        $this->actingAs($this->user)->get($this->gateway->created[0]['cancel'])->assertRedirect(route('cart.index'));

        $this->assertSame(OrderStatus::Failed, $order->refresh()->status);
    }

    public function test_cancel_keeps_a_just_paid_order_pending(): void
    {
        $order = $this->startCheckout(Book::factory()->create());
        $this->gateway->markPaid($order->stripe_checkout_session_id);

        $this->actingAs($this->user)
            ->get(route('checkout.cancel', ['session_id' => $order->stripe_checkout_session_id]))
            ->assertRedirect(route('cart.index'));

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_cancel_cannot_touch_another_customers_order(): void
    {
        $order = $this->startCheckout(Book::factory()->create());

        $this->actingAs(User::factory()->create())
            ->get(route('checkout.cancel', ['order' => $order->id, 'session_id' => $order->stripe_checkout_session_id]))
            ->assertRedirect(route('cart.index'));

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
        $this->assertSame([], $this->gateway->expired);
    }

    public function test_cancel_without_parameters_just_returns_to_the_cart(): void
    {
        $this->actingAs($this->user)->get('/checkout/cancel')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Checkout cancelled. Your books are still in your cart.');
    }

    public function test_refunding_one_of_two_paid_orders_for_a_book_keeps_it_in_the_library(): void
    {
        $book = Book::factory()->create();
        $first = $this->purchase($this->user, $book);
        $second = $this->purchase($this->user, $book); // paid twice (e.g. two tabs)

        $this->assertDatabaseHas('book_user', ['user_id' => $this->user->id, 'book_id' => $book->id, 'order_id' => $first->id]);

        $this->assertSame(0, app(CheckoutService::class)->refund($first));

        $this->assertTrue($this->user->ownsBook($book));
        $this->assertDatabaseHas('book_user', ['user_id' => $this->user->id, 'book_id' => $book->id, 'order_id' => $second->id]);

        $this->assertSame(1, app(CheckoutService::class)->refund($second));
        $this->assertFalse($this->user->ownsBook($book));
    }

    public function test_refunds_only_repoint_to_paid_orders(): void
    {
        $book = Book::factory()->create();
        $other = Book::factory()->create();
        $paid = $this->purchase($this->user, $book, $other);

        // A pending order for the same book must not keep it in the library.
        $pending = app(CheckoutService::class)->createOrder($this->user, collect([$book]));

        $this->assertSame(2, app(CheckoutService::class)->refund($paid));

        $this->assertFalse($this->user->ownsBook($book));
        $this->assertFalse($this->user->ownsBook($other));
        $this->assertSame(OrderStatus::Pending, $pending->refresh()->status);
    }

    public function test_owned_books_dropped_at_checkout_are_announced(): void
    {
        [$owned, $wanted] = Book::factory()->count(2)->create();
        $this->actingAs($this->user)->post('/cart', ['book_id' => $owned->id]);
        $this->actingAs($this->user)->post('/cart', ['book_id' => $wanted->id]);
        $this->purchase($this->user, $owned);

        $this->actingAs($this->user)->post('/checkout')
            ->assertStatus(303)
            ->assertSessionHas('status', 'We took out 1 book you already own.');
    }

    public function test_the_pruned_message_is_plural_and_covers_unavailable_books(): void
    {
        [$a, $b, $c, $d] = Book::factory()->count(4)->create();
        foreach ([$a, $b, $c, $d] as $book) {
            $this->actingAs($this->user)->post('/cart', ['book_id' => $book->id]);
        }
        $this->purchase($this->user, $a, $b);

        $this->actingAs($this->user)->post('/checkout')->assertSessionHas('status', 'We took out 2 books you already own.');

        // Start again with one book that has since been unpublished.
        $e = Book::factory()->create();
        $this->actingAs($this->user)->post('/cart', ['book_id' => $e->id]);
        $e->update(['is_published' => false]);
        $this->actingAs($this->user)->post('/cart', ['book_id' => Book::factory()->create()->id]);

        $this->actingAs($this->user)->post('/checkout')->assertSessionHas('status', 'We took out 1 book that is no longer for sale.');
    }

    public function test_a_cart_emptied_by_pruning_explains_why(): void
    {
        $book = Book::factory()->create();
        $this->actingAs($this->user)->post('/cart', ['book_id' => $book->id]);
        $this->purchase($this->user, $book);

        $this->actingAs($this->user)->post('/checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'We took out 1 book you already own.');

        $this->assertDatabaseCount('orders', 1); // only the purchase() order
    }

    public function test_deleting_an_account_expires_open_checkouts_first(): void
    {
        $order = $this->startCheckout(Book::factory()->create());

        $this->actingAs($this->user)->delete('/profile', ['password' => 'password'])->assertRedirect(route('home'));

        $this->assertSame([$order->stripe_checkout_session_id], $this->gateway->expired);
        $this->assertSame(OrderStatus::Failed, $order->refresh()->status);
        $this->assertNull($order->user_id);
    }
}
