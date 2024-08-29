<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use App\Models\Book;

class CategoryController extends Controller
{
    public function index(Category $category)
    {
        $cart=Cart::content();
        $books = Book::where('category_id', $category->id)
                    ->paginate(6);
        return view('components.MainViews.Category',['cart'=>$cart,'books'=>$books,'category'=>$category]);
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('book_page', 1); // Default to page 1 if not provided
        $categoryId = $request->input('category_id');
        if ($query) {
            // Split the query into individual words
            $keywords = explode(' ', $query);

            // Initialize the query builder
            $queryBuilder = Book::query()->where('category_id', $categoryId)->with('category', 'author');

            // Loop through keywords and add where clauses
            foreach ($keywords as $keyword) {
                $queryBuilder->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%");
                });
            }

            // Get the books matching the search criteria with pagination
            $books = $queryBuilder->paginate(6, ['*'], 'book_page', $page);
        } else {
            // Return all books if no query with pagination
            $books = Book::where('category_id', $categoryId)
                ->with('category', 'author') // Eager load relationships
                ->paginate(6, ['*'], 'book_page', $page);
        }

        return response()->json($books);
    }
}
