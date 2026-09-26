<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookIndexRequest;
use App\Http\Requests\Admin\BookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Services\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BookController extends Controller
{
    public function __construct(private readonly UploadStorage $uploads) {}

    public function index(BookIndexRequest $request): View
    {
        $filters = $request->filters();

        $books = Book::query()
            ->with(['author', 'category'])
            ->withCount(['orderItems as sales_count' => fn (Builder $q) => $q->whereHas('order', fn (Builder $o) => $o->where('status', 'paid'))])
            ->when($filters['q'], function (Builder $query, string $term) {
                $like = '%'.str_replace(['%', '_'], ' ', $term).'%';
                $query->where(fn (Builder $q) => $q
                    ->whereLike('title', $like)
                    ->orWhere('isbn', $term)
                    ->orWhereHas('author', fn (Builder $a) => $a->whereLike('name', $like)));
            })
            ->when($filters['status'], fn (Builder $query, string $status) => match ($status) {
                'published' => $query->published(),
                'draft' => $query->where('is_published', false),
                'scheduled' => $query->where('is_published', true)->where('published_at', '>', now()),
                'featured' => $query->where('is_featured', true),
            })
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.books.create', $this->formData(new Book));
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $book = new Book($request->bookData());
        $book->fill($this->uploads->storeEbook($request->file('ebook')));

        if ($request->hasFile('cover')) {
            $book->cover_path = $this->uploads->storeImage($request->file('cover'), 'covers');
        }

        $book->save();

        return redirect()->route('admin.books.index')
            ->with('status', "“{$book->title}” was created.");
    }

    public function edit(Book $book): View
    {
        return view('admin.books.edit', $this->formData($book));
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $book->fill($request->bookData($book));

        if ($request->hasFile('ebook')) {
            $oldFile = $book->file_path;
            $book->fill($this->uploads->storeEbook($request->file('ebook')));
        }

        if ($request->hasFile('cover')) {
            $oldCover = $book->cover_path;
            $book->cover_path = $this->uploads->storeImage($request->file('cover'), 'covers');
        } elseif ($request->boolean('remove_cover')) {
            $oldCover = $book->cover_path;
            $book->cover_path = null;
        }

        $book->save();

        // Delete replaced files only after the new state is saved.
        $this->uploads->deleteEbook($oldFile ?? null);
        $this->uploads->deleteImage($oldCover ?? null);

        return redirect()->route('admin.books.edit', $book)
            ->with('status', "“{$book->title}” was updated.");
    }

    /**
     * Soft delete: the book leaves the store, but customers who bought it
     * keep it (and its file) in their library.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('status', "“{$book->title}” was removed from the store.");
    }

    /**
     * @return array{book: Book, authors: Collection<int, Author>, categories: Collection<int, Category>}
     */
    private function formData(Book $book): array
    {
        return [
            'book' => $book,
            'authors' => Author::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
