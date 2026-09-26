<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_with_its_data(): void
    {
        $featured = Book::factory()->featured()->create();
        $draft = Book::factory()->draft()->featured()->create();
        $review = Review::factory()->for($featured)->create(['rating' => 5]);
        Review::factory()->for($featured)->create(['rating' => 2]);

        $this->get('/')
            ->assertOk()
            ->assertViewIs('home')
            ->assertViewHas('featured', fn ($books) => $books->contains($featured) && ! $books->contains($draft))
            ->assertViewHas('newReleases', fn ($books) => $books->contains($featured) && ! $books->contains($draft))
            ->assertViewHas('categories', fn ($c) => $c->firstWhere('id', $featured->category_id)->books_count === 1)
            ->assertViewHas('authors', fn ($a) => $a->contains('id', $featured->author_id) && ! $a->contains('id', $draft->author_id))
            ->assertViewHas('reviews', fn ($r) => $r->count() === 1 && $r->first()->is($review))
            ->assertViewHas('settings')
            ->assertViewHas('cartCount', 0);
    }

    public function test_books_index_lists_only_published_books(): void
    {
        $published = Book::factory()->create();
        $draft = Book::factory()->draft()->create();
        $scheduled = Book::factory()->scheduled()->create();
        $deleted = Book::factory()->create();
        $deleted->delete();

        $this->get('/books')
            ->assertOk()
            ->assertViewIs('books.index')
            ->assertViewHas('books', function (LengthAwarePaginator $books) use ($published) {
                return $books->total() === 1 && $books->first()->is($published);
            })
            ->assertViewHas('filters', ['q' => null, 'category' => null, 'author' => null, 'sort' => 'newest']);
    }

    public function test_books_can_be_searched_by_title_description_and_author(): void
    {
        $author = Author::factory()->create(['name' => 'Wilhelmina Quill']);
        $byTitle = Book::factory()->create(['title' => 'The Lantern Keeper']);
        $byAuthor = Book::factory()->for($author)->create(['title' => 'Something Else']);
        $other = Book::factory()->create(['title' => 'Unrelated', 'description' => 'Nothing to see']);

        $this->get('/books?q=lantern')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$byTitle->id]);
        $this->get('/books?q=quill')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$byAuthor->id]);
        $this->get('/books?q=%25')->assertOk(); // wildcards are neutralised
    }

    public function test_books_can_be_filtered_by_category_and_author(): void
    {
        $category = Category::factory()->create(['slug' => 'poetry']);
        $author = Author::factory()->create(['slug' => 'june']);
        $inCategory = Book::factory()->for($category)->create();
        $byAuthor = Book::factory()->for($author)->create();
        Book::factory()->create();

        $this->get('/books?category=poetry')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$inCategory->id]);
        $this->get('/books?author=june')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$byAuthor->id]);
        $this->get('/books?category=poetry&author=june')->assertViewHas('books', fn ($b) => $b->total() === 0);
    }

    public function test_books_can_be_sorted_by_effective_price_and_title(): void
    {
        $cheapOnSale = Book::factory()->price(2000, 300)->create(['title' => 'Bravo']);
        $mid = Book::factory()->price(1000)->create(['title' => 'Alpha']);
        $expensive = Book::factory()->price(1500)->create(['title' => 'Charlie']);

        $this->get('/books?sort=price_asc')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$cheapOnSale->id, $mid->id, $expensive->id]);
        $this->get('/books?sort=price_desc')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$expensive->id, $mid->id, $cheapOnSale->id]);
        $this->get('/books?sort=title')->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$mid->id, $cheapOnSale->id, $expensive->id]);
    }

    public function test_invalid_filters_are_ignored_instead_of_failing(): void
    {
        Book::factory()->create();

        $this->get('/books?sort=drop-table&category[]=x&q='.str_repeat('a', 500))
            ->assertOk()
            ->assertViewHas('filters', fn ($f) => $f['sort'] === 'newest' && $f['category'] === null && mb_strlen($f['q']) === 100);
    }

    public function test_pagination_keeps_the_query_string(): void
    {
        Book::factory()->count(15)->create(['title' => 'Paged book']);

        $this->get('/books?q=paged')->assertViewHas('books', function (LengthAwarePaginator $books) {
            return $books->total() === 15 && str_contains($books->nextPageUrl(), 'q=paged');
        });
    }

    public function test_book_page_shows_a_published_book(): void
    {
        $book = Book::factory()->create();
        Review::factory()->for($book)->count(2)->create();

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertViewIs('books.show')
            ->assertViewHas('book', fn (Book $b) => $b->is($book) && $b->reviews_count === 2)
            ->assertViewHas('reviews', fn ($r) => $r->total() === 2)
            ->assertViewHas('owned', false)
            ->assertViewHas('inCart', false)
            ->assertViewHas('userReview', null)
            ->assertViewHas('canReview', false)
            ->assertViewHas('related');
    }

    public function test_unpublished_scheduled_and_deleted_books_are_not_found_for_the_public(): void
    {
        $draft = Book::factory()->draft()->create();
        $scheduled = Book::factory()->scheduled()->create();
        $deleted = Book::factory()->create();
        $deleted->delete();

        $this->get(route('books.show', $draft))->assertNotFound();
        $this->get(route('books.show', $scheduled))->assertNotFound();
        $this->get('/books/'.$deleted->slug)->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('books.show', $draft))->assertNotFound();
    }

    public function test_admins_can_preview_drafts(): void
    {
        $draft = Book::factory()->draft()->create();

        $this->actingAs($this->admin())->get(route('books.show', $draft))->assertOk();
    }

    public function test_book_page_reflects_ownership_and_review_eligibility(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);

        $this->actingAs($user)->get(route('books.show', $book))
            ->assertViewHas('owned', true)
            ->assertViewHas('canReview', true);

        $review = Review::factory()->for($user)->for($book)->create();

        $this->actingAs($user)->get(route('books.show', $book))
            ->assertViewHas('canReview', false)
            ->assertViewHas('userReview', fn ($r) => $r->is($review));
    }

    public function test_authors_index_lists_authors_with_published_books_and_supports_search(): void
    {
        $visible = Book::factory()->for(Author::factory()->state(['name' => 'Ottoline Ames']))->create();
        Book::factory()->draft()->for(Author::factory()->state(['name' => 'Hidden Draft']))->create();
        Book::factory()->for(Author::factory()->state(['name' => 'Bertram Cole']))->create();

        $this->get('/authors')->assertOk()->assertViewIs('authors.index')
            ->assertViewHas('authors', fn ($a) => $a->total() === 2 && $a->first()->books_count === 1);

        $this->get('/authors?q=ottoline')
            ->assertViewHas('authors', fn ($a) => $a->pluck('id')->all() === [$visible->author_id])
            ->assertViewHas('filters', ['q' => 'ottoline']);
    }

    public function test_author_page_lists_their_published_books(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->for($author)->create();
        Book::factory()->for($author)->draft()->create();

        $this->get(route('authors.show', $author))->assertOk()->assertViewIs('authors.show')
            ->assertViewHas('author', fn ($a) => $a->is($author))
            ->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$book->id]);
    }

    public function test_category_page_lists_its_published_books(): void
    {
        $category = Category::factory()->create();
        $book = Book::factory()->for($category)->create();
        Book::factory()->for($category)->draft()->create();

        $this->get(route('categories.show', $category))->assertOk()->assertViewIs('categories.show')
            ->assertViewHas('category', fn ($c) => $c->is($category))
            ->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$book->id]);
    }

    public function test_deleted_authors_and_categories_are_not_found(): void
    {
        $author = Author::factory()->create();
        $category = Category::factory()->create();
        $author->delete();
        $category->delete();

        $this->get('/authors/'.$author->slug)->assertNotFound();
        $this->get('/categories/'.$category->slug)->assertNotFound();
    }

    public function test_slugs_are_generated_uniquely_including_soft_deleted_rows(): void
    {
        $first = Book::factory()->create(['title' => 'Same Title']);
        $first->delete();
        $second = Book::factory()->create(['title' => 'Same Title']);
        $third = Book::factory()->create(['title' => 'Same Title']);

        $this->assertSame('same-title', $first->slug);
        $this->assertSame('same-title-2', $second->slug);
        $this->assertSame('same-title-3', $third->slug);
    }

    public function test_user_emails_are_never_exposed_on_public_pages(): void
    {
        $user = User::factory()->create(['email' => 'private-reader@example.com']);
        $book = Book::factory()->create();
        Review::factory()->for($user)->for($book)->create(['rating' => 5]);

        $this->get('/')->assertDontSee('private-reader@example.com');
        $this->get(route('books.show', $book))->assertDontSee('private-reader@example.com');
    }
}
