<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk()->assertViewHas('user', fn (User $u) => $u->is($user));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'Test User', 'email' => ' Test@Example.com '])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Saved.')
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'Test User', 'email' => $user->email])->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_update_cannot_escalate_privileges_or_touch_other_users(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Me',
            'email' => $user->email,
            'is_admin' => true,
            'id' => $other->id,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->refresh()->is_admin);
        $this->assertNotSame('Me', $other->refresh()->name);
    }

    public function test_email_must_be_unique(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)->patch('/profile', ['name' => 'X', 'email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_delete_their_account_and_orders_are_kept(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $order = $this->purchase($user, $book);
        $review = Review::factory()->for($user)->for($book)->create();

        $this->actingAs($user)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($review);
        $this->assertDatabaseMissing('book_user', ['book_id' => $book->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => null, 'status' => 'paid']);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->delete('/profile', ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_the_last_admin_cannot_delete_their_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertNotNull($admin->fresh());
    }
}
