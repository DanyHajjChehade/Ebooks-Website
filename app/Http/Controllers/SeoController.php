<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use XMLWriter;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $publishedBooks = fn (Builder $query) => $query->published();

        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $add = function (string $loc, $lastmod = null, string $priority = '0.5') use ($xml) {
            $xml->startElement('url');
            $xml->writeElement('loc', $loc);
            if ($lastmod) {
                $xml->writeElement('lastmod', $lastmod->toAtomString());
            }
            $xml->writeElement('priority', $priority);
            $xml->endElement();
        };

        $add(route('home'), null, '1.0');
        $add(route('books.index'), null, '0.9');
        $add(route('authors.index'), null, '0.6');

        foreach (['pages.contact', 'pages.terms', 'pages.privacy', 'pages.refunds'] as $page) {
            $add(route($page), null, '0.3');
        }

        Book::query()->published()->select(['id', 'slug', 'updated_at'])->orderBy('id')
            ->lazy()->each(fn (Book $book) => $add(route('books.show', $book), $book->updated_at, '0.8'));

        Author::query()->whereHas('books', $publishedBooks)->select(['id', 'slug', 'updated_at'])->orderBy('id')
            ->lazy()->each(fn (Author $author) => $add(route('authors.show', $author), $author->updated_at, '0.5'));

        Category::query()->whereHas('books', $publishedBooks)->select(['id', 'slug', 'updated_at'])->orderBy('id')
            ->lazy()->each(fn (Category $category) => $add(route('categories.show', $category), $category->updated_at, '0.6'));

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Served dynamically so the sitemap URL follows APP_URL. Non-production
     * environments ask crawlers to stay away entirely.
     */
    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        if (app()->isProduction()) {
            array_push(
                $lines,
                'Disallow: /admin',
                'Disallow: /cart',
                'Disallow: /checkout',
                'Disallow: /library',
                'Disallow: /orders',
                'Disallow: /profile',
                '',
                'Sitemap: '.route('sitemap'),
            );
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
