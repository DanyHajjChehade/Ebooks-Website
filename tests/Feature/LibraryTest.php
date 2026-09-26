<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_library_lists_owned_books_including_ones_removed_from_sale(): void
    {
        $user = User::factory()->create();
        [$a, $b, $notOwned] = Book::factory()->count(3)->create();
        $this->purchase($user, $a, $b);
        $b->delete();

        $this->actingAs($user)->get('/library')
            ->assertOk()
            ->assertViewIs('library.index')
            ->assertViewHas('books', fn ($books) => $books->pluck('id')->sort()->values()->all() === [$a->id, $b->id]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $book = Book::factory()->withFile()->create();

        $this->get('/library')->assertRedirect('/login');
        $this->get(route('library.download', $book))->assertRedirect('/login');
    }

    public function test_owners_can_download_their_book_as_an_attachment(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->withFile('%PDF-1.4 owner copy')->create(['slug' => 'the-owner-copy']);
        $this->purchase($user, $book);

        $response = $this->actingAs($user)->get(route('library.download', $book));

        $response->assertOk()
            ->assertDownload('the-owner-copy.pdf')
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('%PDF-1.4 owner copy', $response->streamedContent());
    }

    public function test_non_owners_cannot_download(): void
    {
        $book = Book::factory()->withFile()->create();

        $this->actingAs(User::factory()->create())->get(route('library.download', $book))->assertForbidden();
    }

    public function test_a_pending_or_refunded_order_does_not_grant_downloads(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->withFile()->create();
        $order = $this->purchase($user, $book);
        app(CheckoutService::class)->refund($order);

        $this->actingAs($user)->get(route('library.download', $book))->assertForbidden();
    }

    public function test_admins_can_download_any_book(): void
    {
        $book = Book::factory()->draft()->withFile()->create();

        $this->actingAs($this->admin())->get(route('library.download', $book))->assertOk()->assertDownload();
    }

    public function test_owners_keep_access_to_soft_deleted_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->withFile()->create();
        $this->purchase($user, $book);
        $book->delete();

        $this->actingAs($user)->get(route('library.download', $book))->assertOk()->assertDownload();
        $this->actingAs(User::factory()->create())->get(route('library.download', $book))->assertForbidden();
    }

    public function test_a_missing_file_is_a_404(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(); // no file written
        $this->purchase($user, $book);

        $this->actingAs($user)->get(route('library.download', $book))->assertNotFound();
    }

    public function test_ebook_files_are_not_publicly_reachable(): void
    {
        $book = Book::factory()->withFile()->create();

        $this->get('/storage/'.$book->file_path)->assertNotFound();
        $this->get('/'.$book->file_path)->assertNotFound();
        $this->assertArrayNotHasKey('file_path', $book->toArray());
    }

    public function test_downloads_are_rate_limited(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->withFile()->create();
        $this->purchase($user, $book);
        $this->actingAs($user);

        for ($i = 0; $i < 20; $i++) {
            $this->get(route('library.download', $book))->assertOk();
        }

        $this->get(route('library.download', $book))->assertTooManyRequests();
    }
}
