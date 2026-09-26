<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_shows_defaults_when_no_row_exists(): void
    {
        $this->actingAs($this->admin())->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertViewIs('admin.settings.edit')
            ->assertViewHas('setting', fn (Setting $s) => $s->site_name === config('app.name'));
    }

    public function test_admin_can_update_settings_and_the_cache_is_busted(): void
    {
        Setting::factory()->create(['site_name' => 'Old Name']);
        $this->get('/')->assertViewHas('settings', fn ($s) => $s->site_name === 'Old Name'); // warms the cache

        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'site_name' => 'Book Planet',
            'tagline' => 'Read well',
            'contact_email' => 'hello@example.com',
            'phone' => '+44 20 7946 0000',
            'address' => '1 Library Lane',
            'instagram_url' => 'https://instagram.com/bookplanet',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseCount('settings', 1);
        $this->assertSame('Book Planet', Setting::current()->site_name);
        $this->get('/')->assertViewHas('settings', fn ($s) => $s->site_name === 'Book Planet' && $s->socialLinks() === ['instagram' => 'https://instagram.com/bookplanet']);
        $this->get(route('pages.contact'))->assertSee('1 Library Lane');
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'site_name' => '',
            'contact_email' => 'not-an-email',
            'x_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['site_name', 'contact_email', 'x_url']);
    }
}
