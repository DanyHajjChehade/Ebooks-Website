<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(): View
    {
        $publishedBooks = fn (Builder $query) => $query->published();

        return view('authors.index', [
            'authors' => Author::query()
                ->whereHas('books', $publishedBooks)
                ->withCount(['books' => $publishedBooks])
                ->orderBy('name')
                ->paginate(24),
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
