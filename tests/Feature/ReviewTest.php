<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owners_can_review_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);

        $this->actingAs($user)
            ->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'Wonderful.', 'user_id' => 999])
            ->assertRedirect(route('books.show', $book).'#reviews')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5, 'body' => 'Wonderful.']);
    }

    public function test_non_owners_cannot_review(): void
    {
        $book = Book::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'I never read it.'])
            ->assertForbidden();

        // Authorisation runs before validation: no hint about the rules for non-owners.
        $this->post(route('reviews.store', $book), ['rating' => 9, 'body' => ''])->assertForbidden();
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_guests_cannot_review(): void
    {
        $book = Book::factory()->create();

        $this->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'Anonymous'])->assertRedirect('/login');
    }

    public function test_only_one_review_per_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);
        Review::factory()->for($user)->for($book)->create();

        $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 4, 'body' => 'Again'])->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_review_input_is_validated(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);

        $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 6, 'body' => ''])
            ->assertSessionHasErrors(['rating', 'body']);
        $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 0, 'body' => str_repeat('a', 2001)])
            ->assertSessionHasErrors(['rating', 'body']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_authors_can_edit_and_delete_their_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create(['rating' => 3]);

        $this->actingAs($user)->patch(route('reviews.update', $review), ['rating' => 5, 'body' => 'Changed my mind.'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame(5, $review->refresh()->rating);

        $this->actingAs($user)->delete(route('reviews.destroy', $review))->assertRedirect();
        $this->assertModelMissing($review);
    }

    public function test_other_customers_cannot_edit_or_delete_a_review(): void
    {
        $review = Review::factory()->create(['rating' => 3, 'body' => 'Original']);
        $other = User::factory()->create();

        $this->actingAs($other)->patch(route('reviews.update', $review), ['rating' => 1, 'body' => 'Hijacked'])->assertForbidden();
        $this->actingAs($other)->delete(route('reviews.destroy', $review))->assertForbidden();

        $this->assertSame('Original', $review->refresh()->body);
    }

    public function test_admins_can_delete_but_not_edit_reviews(): void
    {
        $review = Review::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('reviews.update', $review), ['rating' => 1, 'body' => 'Edited by admin'])->assertForbidden();
        $this->actingAs($admin)->delete(route('reviews.destroy', $review))->assertRedirect();

        $this->assertModelMissing($review);
    }

    public function test_review_posting_is_rate_limited(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'Spam'])->assertForbidden();
        }

        $this->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'Spam'])->assertTooManyRequests();
    }
}
