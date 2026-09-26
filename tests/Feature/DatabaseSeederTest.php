<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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

    public function test_the_seeder_refuses_to_run_in_production(): void
    {
        Storage::fake('local');
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--force' => true])
            ->expectsOutputToContain('Demo data is not seeded in production')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_a_production_store_needs_no_seed_data(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(PreventRequestForgery::class);

        // No settings row: pages render with defaults.
        $this->get('/')->assertOk()->assertViewHas('settings', fn (Setting $s) => ! $s->exists && $s->site_name === config('app.name'));

        // The first save in the admin area creates the row.
        $this->actingAs($this->admin())->put(route('admin.settings.update'), ['site_name' => 'My Shop'])->assertSessionHasNoErrors();

        $this->assertSame('My Shop', Setting::sole()->site_name);
        $this->assertSame('My Shop', Setting::current()->site_name);
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
