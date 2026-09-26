<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorRequest;
use App\Http\Requests\SearchRequest;
use App\Models\Author;
use App\Services\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function __construct(private readonly UploadStorage $uploads) {}

    public function index(SearchRequest $request): View
    {
        $filters = $request->filters();

        return view('admin.authors.index', [
            'authors' => Author::query()
                ->withCount('books')
                ->when($filters['q'], fn (Builder $q, string $term) => $q->whereLike('name', '%'.str_replace(['%', '_'], ' ', $term).'%'))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.authors.create', ['author' => new Author]);
    }

    public function store(AuthorRequest $request): RedirectResponse
    {
        $author = new Author($request->authorData());

        if ($request->hasFile('photo')) {
            $author->photo_path = $this->uploads->storeImage($request->file('photo'), 'authors');
        }

        $author->save();

        return redirect()->route('admin.authors.index')
            ->with('status', "{$author->name} was added.");
    }

    public function edit(Author $author): View
    {
        return view('admin.authors.edit', ['author' => $author->loadCount('books')]);
    }

    public function update(AuthorRequest $request, Author $author): RedirectResponse
    {
        $author->fill($request->authorData());

        if ($request->hasFile('photo')) {
            $oldPhoto = $author->photo_path;
            $author->photo_path = $this->uploads->storeImage($request->file('photo'), 'authors');
        } elseif ($request->boolean('remove_photo')) {
            $oldPhoto = $author->photo_path;
            $author->photo_path = null;
        }

        $author->save();

        $this->uploads->deleteImage($oldPhoto ?? null);

        return redirect()->route('admin.authors.edit', $author)
            ->with('status', "{$author->name} was updated.");
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->books()->exists()) {
            return back()->withErrors(['delete' => "{$author->name} still has books in the store. Reassign or delete them first."]);
        }

        $author->delete();

        return redirect()->route('admin.authors.index')
            ->with('status', "{$author->name} was deleted.");
    }
}
