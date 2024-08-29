<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use App\Models\Book;

class AuthorController extends Controller
{
    public function index(Author $author)
    {

        $cart=Cart::content();
        $books = Book::where('author_id', $author->id)
                    ->paginate(6);
        return view ('components.MainViews.Author',['cart'=>$cart,'books'=>$books,'author'=>$author]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('page', 1); // Default to page 1 if not provided

        if ($query) {
            // Split the query into individual words
            $keywords = explode(' ', $query);

            // Initialize the query builder
            $queryBuilder = Author::query();

            // Loop through keywords and add where clauses
            foreach ($keywords as $keyword) {
                $queryBuilder->where(function ($query) use ($keyword) {
                    $query->where('first_name', 'LIKE', "%{$keyword}%")
                          ->orWhere('last_name', 'LIKE', "%{$keyword}%");
                });
            }

            // Get the authors matching the search criteria with pagination
            $authors = $queryBuilder->paginate(4, ['*'], 'page', $page);
        } else {
            // Return all authors if no query with pagination
            $authors = Author::paginate(4, ['*'], 'page', $page);
        }

        return response()->json($authors);
    }
    public function searchbooks(Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('book_page', 1); // Default to page 1 if not provided
        $authorId = $request->input('author_id');
        if ($query) {
            // Split the query into individual words
            $keywords = explode(' ', $query);

            // Initialize the query builder
            $queryBuilder = Book::query()->where('author_id', $authorId)->with('category', 'author');

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
            $books = Book::where('author_id', $authorId)
                ->with('category', 'author') // Eager load relationships
                ->paginate(6, ['*'], 'book_page', $page);
        }

        return response()->json($books);
    }

}
