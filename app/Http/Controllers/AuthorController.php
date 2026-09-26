<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(SearchRequest $request): View
    {
        $filters = $request->filters();
        $publishedBooks = fn (Builder $query) => $query->published();

        return view('authors.index', [
            'authors' => Author::query()
                ->whereHas('books', $publishedBooks)
                ->withCount(['books' => $publishedBooks])
                ->when($filters['q'], fn (Builder $q, string $term) => $q->whereLike('name', '%'.str_replace(['%', '_'], ' ', $term).'%'))
                ->orderBy('name')
                ->paginate(24)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(Author $author): View
    {
        return view('authors.show', [
            'author' => $author,
            'books' => $author->books()
                ->published()
                ->with(['author', 'category'])
                ->withAvg('reviews', 'rating')
                ->latest('published_at')
                ->paginate(config('bookplanet.per_page')),
        ]);
    }
}
