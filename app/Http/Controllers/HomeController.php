<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $publishedBooks = fn (Builder $query) => $query->published();

        return view('home', [
            'featured' => Book::query()->published()
                ->where('is_featured', true)
                ->with('author')
                ->withAvg('reviews', 'rating')
                ->latest('published_at')
                ->take(8)
                ->get(),
            'newReleases' => Book::query()->published()
                ->with('author')
                ->withAvg('reviews', 'rating')
                ->latest('published_at')
                ->latest('id')
                ->take(8)
                ->get(),
            'categories' => Category::query()
                ->withCount(['books' => $publishedBooks])
                ->orderBy('name')
                ->get(),
            'authors' => Author::query()
                ->whereHas('books', $publishedBooks)
                ->withCount(['books' => $publishedBooks])
                ->orderByDesc('books_count')
                ->orderBy('name')
                ->take(8)
                ->get(),
            'reviews' => Review::query()
                ->where('rating', '>=', 4)
                ->whereHas('book', $publishedBooks)
                ->with(['user:id,name', 'book.author'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
