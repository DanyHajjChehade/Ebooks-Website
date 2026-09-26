<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_published_content_only(): void
    {
        $published = Book::factory()->create();
        $draft = Book::factory()->draft()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('books.show', $published), false)
            ->assertSee(route('authors.show', $published->author), false)
            ->assertSee(route('categories.show', $published->category), false)
            ->assertSee(route('pages.terms'), false)
            ->assertDontSee(route('books.show', $draft), false)
            ->assertDontSee(route('authors.show', $draft->author), false);
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /');
    }

    public function test_robots_points_to_the_sitemap_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_legal_pages_render(): void
    {
        foreach (['pages.terms' => 'pages.terms', 'pages.privacy' => 'pages.privacy', 'pages.refunds' => 'pages.refunds', 'pages.contact' => 'pages.contact'] as $route => $view) {
            $this->get(route($route))->assertOk()->assertViewIs($view)->assertViewHas('settings');
        }
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; object-src 'none'");
    }
}
