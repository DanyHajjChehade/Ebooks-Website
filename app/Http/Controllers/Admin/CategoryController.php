<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(SearchRequest $request): View
    {
        $filters = $request->filters();

        return view('admin.categories.index', [
            'categories' => Category::query()
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
        return view('admin.categories.create', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', "{$category->name} was created.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.edit', $category)
            ->with('status', "{$category->name} was updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->books()->exists()) {
            return back()->with('error', "{$category->name} still has books. Move them to another category first.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', "{$category->name} was deleted.");
    }
}
