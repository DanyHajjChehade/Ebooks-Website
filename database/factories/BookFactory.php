<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Published by default. The file_path points at a random name; tests that
 * download should put a file there (see withFile()).
 *
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(3), '.'),
            'description' => fake()->paragraphs(2, true),
            'author_id' => Author::factory(),
            'category_id' => Category::factory(),
            'price_cents' => fake()->numberBetween(399, 2499),
            'sale_price_cents' => null,
            'cover_path' => null,
            'file_path' => 'ebooks/'.Str::random(40).'.pdf',
            'file_format' => 'pdf',
            'file_size' => fake()->numberBetween(50_000, 5_000_000),
            'page_count' => fake()->numberBetween(80, 600),
            'isbn' => fake()->isbn13(),
            'is_published' => true,
            'is_featured' => false,
            'published_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false, 'published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->addWeek()]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function price(int $cents, ?int $saleCents = null): static
    {
        return $this->state(['price_cents' => $cents, 'sale_price_cents' => $saleCents]);
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price_cents' => (int) floor($attributes['price_cents'] * 0.6),
        ]);
    }

    public function free(): static
    {
        return $this->state(['price_cents' => 0, 'sale_price_cents' => null]);
    }

    public function epub(): static
    {
        return $this->state(['file_path' => 'ebooks/'.Str::random(40).'.epub', 'file_format' => 'epub']);
    }

    /**
     * Also write a small file to the ebook disk at file_path.
     */
    public function withFile(string $contents = "%PDF-1.4\n%fake\n"): static
    {
        return $this->afterCreating(function (Book $book) use ($contents) {
            Storage::disk(config('bookplanet.ebook_disk'))->put($book->file_path, $contents);
        });
    }
}
