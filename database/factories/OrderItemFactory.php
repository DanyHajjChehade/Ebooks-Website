<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'book_id' => Book::factory(),
            'title' => fn (array $attributes) => Book::find($attributes['book_id'])?->title ?? fake()->sentence(3),
            'price_cents' => fn (array $attributes) => Book::find($attributes['book_id'])?->effective_price_cents ?? 999,
        ];
    }
}
