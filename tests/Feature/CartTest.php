<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_add_books_to_the_cart(): void
    {
        $book = Book::factory()->create(['title' => 'The Salt Cartographer']);

        $this->from(route('books.show', $book))
            ->post('/cart', ['book_id' => $book->id])
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHas('status', 'Added “The Salt Cartographer” to your cart.')
            ->assertSessionHas(Cart::SESSION_KEY, [$book->id]);

        $this->get('/cart')
            ->assertOk()
            ->assertViewIs('cart.index')
            ->assertViewHas('items', fn ($items) => $items->pluck('id')->all() === [$book->id])
            ->assertViewHas('subtotalCents', $book->price_cents)
            ->assertViewHas('cartCount', 1)
            ->assertViewHas('cartBookIds', [$book->id])
            ->assertViewHas('ownedBookIds', []);
    }

    public function test_a_book_is_only_added_once(): void
    {
        $book = Book::factory()->create();

        $this->post('/cart', ['book_id' => $book->id]);
        $this->post('/cart', ['book_id' => $book->id])->assertSessionHas('status');

        $this->assertSame([$book->id], session(Cart::SESSION_KEY));
    }

    public function test_owned_books_are_not_added(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);

        $this->actingAs($user)->post('/cart', ['book_id' => $book->id])
            ->assertSessionHas('error', 'You already own this book. It’s in your library.');

        $this->assertSame([], session(Cart::SESSION_KEY, []));
    }

    public function test_unpublished_books_are_ignored(): void
    {
        $draft = Book::factory()->draft()->create();

        $this->post('/cart', ['book_id' => $draft->id])->assertSessionHas('error');

        $this->assertSame([], session(Cart::SESSION_KEY, []));
    }

    public function test_cart_input_is_validated(): void
    {
        $deleted = Book::factory()->create();
        $deleted->delete();

        $this->post('/cart', [])->assertSessionHasErrors('book_id');
        $this->post('/cart', ['book_id' => 'abc'])->assertSessionHasErrors('book_id');
        $this->post('/cart', ['book_id' => [1, 2]])->assertSessionHasErrors('book_id');
        $this->post('/cart', ['book_id' => 999])->assertSessionHasErrors('book_id');
        $this->post('/cart', ['book_id' => $deleted->id])->assertSessionHasErrors('book_id');
    }

    public function test_json_requests_get_the_count(): void
    {
        $book = Book::factory()->create();

        $this->postJson('/cart', ['book_id' => $book->id])
            ->assertOk()
            ->assertJson(['count' => 1, 'added' => true])
            ->assertJsonStructure(['count', 'message']);

        $this->deleteJson('/cart/'.$book->id)
            ->assertOk()
            ->assertJson(['count' => 0]);
    }

    public function test_books_can_be_removed(): void
    {
        $book = Book::factory()->create();
        $other = Book::factory()->create();

        $this->post('/cart', ['book_id' => $book->id]);
        $this->post('/cart', ['book_id' => $other->id]);

        $this->delete('/cart/'.$book->id)->assertRedirect()->assertSessionHas('status', "Removed “{$book->title}” from your cart.");

        $this->assertSame([$other->id], session(Cart::SESSION_KEY));
    }

    public function test_books_that_become_unavailable_or_owned_drop_out_of_the_cart(): void
    {
        $user = User::factory()->create();
        [$a, $b, $c] = Book::factory()->count(3)->create();

        $this->actingAs($user);
        foreach ([$a, $b, $c] as $book) {
            $this->post('/cart', ['book_id' => $book->id]);
        }

        $a->update(['is_published' => false]);
        $this->purchase($user, $b);

        $this->get('/cart')->assertViewHas('items', fn ($items) => $items->pluck('id')->all() === [$c->id]);
        $this->assertSame([$c->id], session(Cart::SESSION_KEY));
    }

    public function test_subtotal_uses_current_sale_prices(): void
    {
        $book = Book::factory()->price(2000)->create();
        $this->post('/cart', ['book_id' => $book->id]);

        $book->update(['sale_price_cents' => 1200]);

        $this->get('/cart')->assertViewHas('subtotalCents', 1200);
    }

    public function test_cart_survives_login(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->post('/cart', ['book_id' => $book->id]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->get('/cart')->assertViewHas('items', fn ($items) => $items->count() === 1);
    }

    public function test_shared_view_data_marks_owned_and_carted_books(): void
    {
        $user = User::factory()->create();
        [$owned, $carted] = Book::factory()->count(2)->create();
        $this->purchase($user, $owned);

        $this->actingAs($user)->post('/cart', ['book_id' => $carted->id]);

        $this->get('/books')
            ->assertViewHas('ownedBookIds', [$owned->id])
            ->assertViewHas('cartBookIds', [$carted->id])
            ->assertViewHas('cartCount', 1);
    }
}
