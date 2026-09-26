<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_status_and_search(): void
    {
        $alice = User::factory()->create(['email' => 'alice@example.com']);
        $paid = $this->purchase($alice, Book::factory()->create());
        $pending = Order::factory()->create();

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.orders.index'))
            ->assertOk()->assertViewIs('admin.orders.index')
            ->assertViewHas('orders', fn ($o) => $o->total() === 2)
            ->assertViewHas('filters', ['q' => null, 'status' => null]);

        $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'pending']))
            ->assertViewHas('orders', fn ($o) => $o->pluck('id')->all() === [$pending->id]);
        $this->actingAs($admin)->get(route('admin.orders.index', ['q' => 'alice@']))
            ->assertViewHas('orders', fn ($o) => $o->pluck('id')->all() === [$paid->id]);
        $this->actingAs($admin)->get(route('admin.orders.index', ['q' => $paid->reference]))
            ->assertViewHas('orders', fn ($o) => $o->pluck('id')->all() === [$paid->id]);
        $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'bogus']))
            ->assertOk()->assertViewHas('filters', ['q' => null, 'status' => null]);
    }

    public function test_admin_can_view_any_order(): void
    {
        $order = $this->purchase(User::factory()->create(), Book::factory()->create());

        $this->actingAs($this->admin())->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertViewIs('admin.orders.show')
            ->assertViewHas('order', fn ($o) => $o->is($order) && $o->relationLoaded('user'));
    }

    public function test_admin_can_refund_a_paid_order(): void
    {
        $gateway = $this->fakeGateway();
        $customer = User::factory()->create();
        $book = Book::factory()->create();
        $order = $this->purchase($customer, $book);

        $this->actingAs($this->admin())->post(route('admin.orders.refund', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status');

        $this->assertSame([$order->id], $gateway->refunded);
        $this->assertSame(OrderStatus::Refunded, $order->refresh()->status);
        $this->assertFalse($customer->ownsBook($book));

        // The charge.refunded webhook that follows is a no-op.
        $this->postStripeEvent('charge.refunded', [
            'object' => 'charge', 'payment_intent' => $order->stripe_payment_intent_id,
            'amount' => $order->subtotal_cents, 'amount_refunded' => $order->subtotal_cents, 'refunded' => true,
        ])->assertOk();
        $this->assertSame(OrderStatus::Refunded, $order->refresh()->status);
    }

    public function test_a_failed_refund_changes_nothing(): void
    {
        $gateway = $this->fakeGateway();
        $gateway->failRefund = true;
        $customer = User::factory()->create();
        $book = Book::factory()->create();
        $order = $this->purchase($customer, $book);

        $this->actingAs($this->admin())->post(route('admin.orders.refund', $order))->assertSessionHas('error');

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
        $this->assertTrue($customer->ownsBook($book));
    }

    public function test_only_paid_orders_can_be_refunded(): void
    {
        $gateway = $this->fakeGateway();
        $order = Order::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.orders.refund', $order))->assertSessionHas('error');

        $this->assertSame([], $gateway->refunded);
        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }
}
