<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookIndexRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Services\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(BookIndexRequest $request): View
    {
        $filters = $request->filters();
        $publishedBooks = fn (Builder $query) => $query->published();

        return view('books.index', [
            'books' => Book::query()
                ->published()
                ->filter($filters)
                ->with(['author', 'category'])
                ->withAvg('reviews', 'rating')
                ->paginate(config('bookplanet.per_page'))
                ->withQueryString(),
            'categories' => Category::query()
                ->withCount(['books' => $publishedBooks])
                ->orderBy('name')
                ->get(),
            'authors' => Author::query()
                ->whereHas('books', $publishedBooks)
                ->orderBy('name')
                ->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Book $book, Cart $cart): View
    {
        // Drafts 404 for the public; admins can preview them.
        abort_unless(Gate::allows('view', $book), 404);

        $book->load(['author', 'category'])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        $user = $request->user();
        $owned = $user?->ownsBook($book) ?? false;

        return view('books.show', [
            'book' => $book,
            'reviews' => $book->reviews()
                ->with('user:id,name')
                ->latest()
                ->paginate(6)
                ->fragment('reviews'),
            'related' => Book::query()
                ->published()
                ->whereKeyNot($book->getKey())
                ->where(fn (Builder $q) => $q
                    ->where('category_id', $book->category_id)
                    ->orWhere('author_id', $book->author_id))
                ->with('author')
                ->withAvg('reviews', 'rating')
                ->latest('published_at')
                ->take(4)
                ->get(),
            'owned' => $owned,
            'inCart' => ! $owned && $cart->has($book),
            'userReview' => $user ? $book->reviews()->whereBelongsTo($user)->first() : null,
            'canReview' => $user !== null && Gate::forUser($user)->allows('create', [Review::class, $book]),
        ]);
    }
}
