<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create())->get('/confirm-password')->assertOk()->assertViewIs('auth.confirm-password');
    }

    public function test_password_can_be_confirmed(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/confirm-password', ['password' => 'password'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/confirm-password', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');
    }
}
