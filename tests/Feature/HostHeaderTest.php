<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Review finding B1: absolute URLs (password-reset links, sitemap, Stripe
 * return URLs) must never be built from a forged Host header.
 */
class HostHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Trusted hosts are static Symfony state; don't leak them into other tests.
        Request::setTrustedHosts([]);

        parent::tearDown();
    }

    /**
     * Behave like a production server whose APP_URL is https://shop.example.
     */
    private function asProduction(string $appUrl = 'https://shop.example'): void
    {
        config(['app.url' => $appUrl]);
        $this->app['env'] = 'production';
        // Outside "testing" Laravel enforces CSRF again; it is not what these tests are about.
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->app->getProvider(AppServiceProvider::class)->configureUrls();
    }

    public function test_a_foreign_host_cannot_trigger_a_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->asProduction();

        $this->withHeader('Host', 'evil.example')
            ->post('https://evil.example/forgot-password', ['email' => $user->email])
            ->assertStatus(400);

        Notification::assertNothingSent();
    }

    public function test_reset_links_use_app_url_on_the_real_host(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->asProduction();

        $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;

            return str_starts_with($url, 'https://shop.example/reset-password/');
        });
    }

    public function test_urls_are_built_from_app_url_even_if_the_host_header_lies(): void
    {
        $this->asProduction();

        // Second line of defence: even a request object carrying a foreign host
        // (e.g. one that bypassed trustHosts) generates APP_URL links.
        URL::setRequest(Request::create('http://evil.example/forgot-password'));

        $this->assertSame('https://shop.example/reset-password/token', route('password.reset', 'token'));
        $this->assertSame('https://shop.example/checkout/success', route('checkout.success'));
    }

    public function test_sitemap_uses_app_url(): void
    {
        $book = Book::factory()->create(['slug' => 'the-salt-cartographer']);
        $this->asProduction();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>https://shop.example/books/the-salt-cartographer</loc>', false)
            ->assertDontSee('http://localhost', false);

        $this->withHeader('Host', 'evil.example')->get('https://evil.example/sitemap.xml')->assertStatus(400);
    }

    public function test_robots_sitemap_line_uses_app_url(): void
    {
        $this->asProduction();

        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://shop.example/sitemap.xml');
    }

    public function test_the_configured_host_is_matched_exactly(): void
    {
        $this->asProduction();

        // Unanchored patterns would let these through.
        $this->withHeader('Host', 'shop.example.attacker.net')->get('https://shop.example.attacker.net/')->assertStatus(400);
        $this->withHeader('Host', 'shopXexample')->get('https://shopXexample/')->assertStatus(400);
    }

    public function test_host_checks_are_off_without_an_app_url_host(): void
    {
        $this->asProduction('');

        $this->get('/')->assertOk();
    }
}
