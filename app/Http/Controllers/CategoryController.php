<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        return view('categories.show', [
            'category' => $category,
            'books' => $category->books()
                ->published()
                ->with(['author', 'category'])
                ->withAvg('reviews', 'rating')
                ->latest('published_at')
                ->paginate(config('bookplanet.per_page')),
        ]);
    }
}
