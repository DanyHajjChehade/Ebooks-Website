<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    private function adminPages(): array
    {
        $book = Book::factory()->create();
        $order = Order::factory()->create();

        return [
            route('admin.dashboard'),
            route('admin.books.index'),
            route('admin.books.create'),
            route('admin.books.edit', $book),
            route('admin.authors.index'),
            route('admin.authors.create'),
            route('admin.authors.edit', $book->author),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $book->category),
            route('admin.orders.index'),
            route('admin.orders.show', $order),
            route('admin.users.index'),
            route('admin.reviews.index'),
            route('admin.settings.edit'),
        ];
    }

    public function test_guests_are_redirected_to_login(): void
    {
        foreach ($this->adminPages() as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_customers_are_forbidden_everywhere(): void
    {
        $customer = User::factory()->create();
        $pages = $this->adminPages();

        foreach ($pages as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }

        $book = Book::first();
        $this->actingAs($customer)->post(route('admin.books.store'), [])->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.books.destroy', $book))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.users.toggle-admin', $customer))->assertForbidden();
        $this->actingAs($customer)->put(route('admin.settings.update'), ['site_name' => 'Pwned'])->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.reviews.destroy', Review::factory()->create()))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.orders.refund', Order::first()))->assertForbidden();

        $this->assertFalse($customer->refresh()->is_admin);
        $this->assertNotSoftDeleted($book);
    }

    public function test_admins_can_open_every_page(): void
    {
        $admin = $this->admin();

        foreach ($this->adminPages() as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_dashboard_reports_stats(): void
    {
        $customer = User::factory()->create();
        $book = Book::factory()->price(1500)->create();
        Book::factory()->draft()->create();
        $this->purchase($customer, $book);

        $old = $this->purchase(User::factory()->create(), Book::factory()->price(900)->create());
        $old->forceFill(['paid_at' => now()->subDays(45)])->save();
        Order::factory()->create(); // pending

        $this->actingAs($this->admin())->get(route('admin.dashboard'))
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('stats', [
                'revenue_cents_30d' => 1500,
                'paid_orders_30d' => 1,
                'customers' => 3,
                'published_books' => 2,
            ])
            ->assertViewHas('recentOrders', fn ($orders) => $orders->count() === 3)
            ->assertViewHas('topBooks', fn ($books) => $books->count() === 2 && $books->first()->sales_count === 1);
    }
}
