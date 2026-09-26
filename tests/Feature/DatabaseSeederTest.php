<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_catalogue_is_seeded_and_the_seeder_can_run_again(): void
    {
        Storage::fake('local');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class); // re-running must not fail or duplicate

        $this->assertSame(8, Category::count());
        $this->assertSame(12, Author::count());
        $this->assertSame(40, Book::published()->count());
        $this->assertSame(2, Book::published()->where('price_cents', 0)->count());
        $this->assertGreaterThanOrEqual(5, Book::published()->whereNotNull('sale_price_cents')->count());
        $this->assertSame(1, Setting::count());

        $this->assertTrue(User::where('email', 'admin@bookplanet.test')->sole()->is_admin);
        $reader = User::where('email', 'reader@bookplanet.test')->sole();
        $this->assertFalse($reader->is_admin);
        $this->assertSame(3, $reader->books()->count());
        $this->assertGreaterThanOrEqual(1, $reader->reviews()->count());

        Book::all()->each(function (Book $book) {
            Storage::disk('local')->assertExists($book->file_path);
            $this->assertStringStartsWith('%PDF-1.4', Storage::disk('local')->get($book->file_path));
            $this->assertTrue($book->price_cents === 0 || $book->price_cents >= 50);
        });

        $this->assertGreaterThan(0, Order::where('status', 'paid')->count());
    }

    public function test_seeded_pages_render(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $reader = User::where('email', 'reader@bookplanet.test')->sole();
        $book = $reader->books()->first();

        $this->get('/')->assertOk();
        $this->get('/books')->assertOk();
        $this->actingAs($reader)->get(route('books.show', $book))->assertOk()->assertViewHas('owned', true);
        $this->actingAs($reader)->get(route('library.download', $book))->assertOk()->assertDownload();
    }
}
