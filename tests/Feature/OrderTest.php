<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_see_only_their_own_orders(): void
    {
        $user = User::factory()->create();
        $mine = $this->purchase($user, Book::factory()->create());
        $theirs = $this->purchase(User::factory()->create(), Book::factory()->create());

        $this->actingAs($user)->get('/orders')
            ->assertOk()
            ->assertViewIs('orders.index')
            ->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$mine->id]);
    }

    public function test_customers_can_view_their_order(): void
    {
        $user = User::factory()->create();
        $order = $this->purchase($user, Book::factory()->create());

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertViewIs('orders.show')
            ->assertViewHas('order', fn ($o) => $o->is($order) && $o->relationLoaded('items'));
    }

    public function test_customers_cannot_view_other_peoples_orders(): void
    {
        $order = $this->purchase(User::factory()->create(), Book::factory()->create());

        $this->get(route('orders.show', $order))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_orders_cannot_be_modified_by_customers(): void
    {
        $user = User::factory()->create();
        $order = $this->purchase($user, Book::factory()->create());

        $this->actingAs($user)->delete(route('orders.show', $order))->assertMethodNotAllowed();
        $this->actingAs($user)->post(route('orders.show', $order).'/cancel')->assertNotFound();
    }
}
