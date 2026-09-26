<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'subtotal_cents' => 0,
            'currency' => 'usd',
            'stripe_checkout_session_id' => 'cs_test_'.Str::random(24),
            'stripe_payment_intent_id' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Paid,
            'stripe_payment_intent_id' => 'pi_test_'.Str::random(24),
            'paid_at' => now(),
        ]);
    }

    /**
     * Add one item per book, priced at the book's current price, and keep the
     * subtotal in sync. Paid orders also grant library access.
     *
     * @param  iterable<Book>  $books
     */
    public function withBooks(iterable $books): static
    {
        return $this->afterCreating(function (Order $order) use ($books) {
            $total = 0;

            foreach ($books as $book) {
                $order->items()->create([
                    'book_id' => $book->getKey(),
                    'title' => $book->title,
                    'price_cents' => $book->effective_price_cents,
                ]);
                $total += $book->effective_price_cents;

                if ($order->isPaid() && $order->user_id) {
                    $order->user->books()->syncWithoutDetaching([$book->getKey() => ['order_id' => $order->getKey()]]);
                }
            }

            $order->forceFill(['subtotal_cents' => $total])->save();
        });
    }
}
