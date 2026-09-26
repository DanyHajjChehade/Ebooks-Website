<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_promotes_an_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $this->artisan('app:make-admin', ['email' => 'OWNER@example.com'])->assertSuccessful();

        $this->assertTrue($user->refresh()->is_admin);
    }

    public function test_it_creates_a_new_admin_when_given_a_password(): void
    {
        $this->artisan('app:make-admin', ['email' => 'new@example.com', '--name' => 'New Admin', '--password' => 'secret-password'])
            ->assertSuccessful();

        $user = User::where('email', 'new@example.com')->sole();
        $this->assertTrue($user->is_admin);
        $this->assertSame('New Admin', $user->name);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_it_rejects_invalid_input(): void
    {
        $this->artisan('app:make-admin', ['email' => 'not-an-email'])->assertFailed();
        $this->artisan('app:make-admin', ['email' => 'short@example.com', '--password' => 'short'])->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_is_idempotent(): void
    {
        $this->admin(['email' => 'already@example.com']);

        $this->artisan('app:make-admin', ['email' => 'already@example.com'])->assertSuccessful();
    }
}
