<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_supports_search_and_role_filter(): void
    {
        $admin = $this->admin(['name' => 'Root Admin']);
        $reader = User::factory()->create(['name' => 'Rita Reader', 'email' => 'rita@example.com']);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()->assertViewIs('admin.users.index')
            ->assertViewHas('users', fn ($u) => $u->total() === 2);
        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'rita']))
            ->assertViewHas('users', fn ($u) => $u->pluck('id')->all() === [$reader->id]);
        $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'admin']))
            ->assertViewHas('users', fn ($u) => $u->pluck('id')->all() === [$admin->id]);
    }

    public function test_admin_can_promote_and_demote_other_users(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.toggle-admin', $user))->assertRedirect()->assertSessionHas('status');
        $this->assertTrue($user->refresh()->is_admin);

        $this->actingAs($admin)->patch(route('admin.users.toggle-admin', $user))->assertRedirect();
        $this->assertFalse($user->refresh()->is_admin);
    }

    public function test_admins_cannot_demote_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.users.toggle-admin', $admin))->assertSessionHas('error');

        $this->assertTrue($admin->refresh()->is_admin);
    }
}
