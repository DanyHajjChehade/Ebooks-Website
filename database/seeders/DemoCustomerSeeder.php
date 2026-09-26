<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Orders, libraries and reviews so the storefront and the admin dashboard
 * look lived-in. Deterministic (seeded RNG).
 */
class DemoCustomerSeeder extends Seeder
{
    private const CUSTOMERS = [
        ['Amelia Hart', 'amelia.hart@example.com'],
        ['Jonah Reyes', 'jonah.reyes@example.com'],
        ['Sofia Brennan', 'sofia.brennan@example.com'],
        ['Kenji Watanabe', 'kenji.watanabe@example.com'],
        ['Lucia Moreno', 'lucia.moreno@example.com'],
        ['Felix Grant', 'felix.grant@example.com'],
        ['Hannah Osei', 'hannah.osei@example.com'],
        ['Ruth Delaney', 'ruth.delaney@example.com'],
    ];

    /** @var array<int, list<string>> */
    private array $reviewPool;

    public function run(): void
    {
        if (Order::query()->exists()) {
            $this->command?->warn('Orders already exist; skipping demo customers (use migrate:fresh --seed).');

            return;
        }

        $this->reviewPool = (require __DIR__.'/data/catalogue.php')['reviews'];
        mt_srand(2026);

        $books = Book::query()->published()->orderBy('id')->get()->keyBy('slug');
        $paidBooks = $books->filter(fn (Book $book) => $book->effective_price_cents > 0)->values();

        // The demo customer: owns three books, has reviewed two of them.
        $reader = User::query()->where('email', 'reader@bookplanet.test')->firstOrFail();
        $this->order($reader, collect([$books['the-glasshouse-year']]), now()->subDays(34));
        $this->order($reader, collect([$books['the-salt-cartographer'], $books['the-ninth-bell']]), now()->subDays(12));
        $this->review($reader, $books['the-salt-cartographer'], 5, 'I read this in two evenings and have been thinking about the island ever since. The final chapter is quietly devastating.', now()->subDays(8));
        $this->review($reader, $books['the-ninth-bell'], 4, 'A proper fair-play puzzle with a wonderfully wintry atmosphere. I guessed the how but not the who.', now()->subDays(5));

        foreach (self::CUSTOMERS as $i => [$name, $email]) {
            $customer = User::query()->firstOrNew(['email' => $email]);
            $customer->fill(['name' => $name, 'password' => Str::password(24)]);
            $customer->email_verified_at = now()->subDays(90);
            $customer->save();

            $owned = collect();
            $orderCount = 1 + $i % 2;

            for ($o = 0; $o < $orderCount; $o++) {
                $picks = $this->pick($paidBooks, 1 + mt_rand(0, 2), $owned);
                $owned = $owned->merge($picks->pluck('id'));
                $this->order($customer, $picks, now()->subDays(mt_rand(1, 58))->subMinutes(mt_rand(0, 600)));
            }

            // Everyone reviews one or two of their books; mostly warm, occasionally lukewarm.
            foreach ($customer->books()->orderBy('books.id')->take(1 + $i % 2)->get() as $book) {
                $rating = [5, 5, 4, 5, 4, 3, 5, 4][($i + $book->getKey()) % 8];
                $bodies = $this->reviewPool[$rating];
                $this->review($customer, $book, $rating, $bodies[($i + $book->getKey()) % count($bodies)], now()->subDays(mt_rand(0, 20)));
            }
        }

        // A little variety for the admin orders screen.
        $amelia = User::query()->where('email', 'amelia.hart@example.com')->first();
        $this->order($amelia, $this->pick($paidBooks, 1, $amelia->ownedBookIds()), now()->subHours(3), OrderStatus::Pending);
        $jonah = User::query()->where('email', 'jonah.reyes@example.com')->first();
        $this->order($jonah, $this->pick($paidBooks, 1, $jonah->ownedBookIds()), now()->subDays(20), OrderStatus::Refunded);
    }

    /**
     * @param  Collection<int, Book>  $books
     * @param  iterable<int>  $exclude
     * @return Collection<int, Book>
     */
    private function pick(Collection $books, int $count, iterable $exclude): Collection
    {
        $exclude = collect($exclude)->all();

        return $books->reject(fn (Book $book) => in_array($book->getKey(), $exclude, true))
            ->shuffle(mt_rand())
            ->take($count)
            ->values();
    }

    /**
     * @param  Collection<int, Book>  $books
     */
    private function order(User $user, Collection $books, Carbon $at, OrderStatus $status = OrderStatus::Paid): Order
    {
        $subtotal = (int) $books->sum(fn (Book $book) => $book->effective_price_cents);
        $viaStripe = $subtotal > 0;

        $order = new Order([
            'status' => $status,
            'subtotal_cents' => $subtotal,
            'currency' => config('services.stripe.currency', 'usd'),
            'stripe_checkout_session_id' => $viaStripe ? 'cs_test_demo_'.Str::lower(Str::random(20)) : null,
            'stripe_payment_intent_id' => $viaStripe && $status !== OrderStatus::Pending ? 'pi_demo_'.Str::lower(Str::random(20)) : null,
            'paid_at' => $status === OrderStatus::Pending ? null : $at,
        ]);
        $order->user()->associate($user);
        $order->created_at = $at;
        $order->updated_at = $at;
        $order->save();

        $order->items()->createMany($books->map(fn (Book $book) => [
            'book_id' => $book->getKey(),
            'title' => $book->title,
            'price_cents' => $book->effective_price_cents,
        ])->all());

        if ($status === OrderStatus::Paid) {
            DB::table('book_user')->insertOrIgnore($books->map(fn (Book $book) => [
                'user_id' => $user->getKey(),
                'book_id' => $book->getKey(),
                'order_id' => $order->getKey(),
                'created_at' => $at,
                'updated_at' => $at,
            ])->all());
        }

        return $order;
    }

    private function review(User $user, Book $book, int $rating, string $body, Carbon $at): void
    {
        $review = new Review(['rating' => $rating, 'body' => $body]);
        $review->user()->associate($user);
        $review->book()->associate($book);
        $review->created_at = $at;
        $review->updated_at = $at;
        $review->save();
    }
}
