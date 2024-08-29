<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('book_page', 1); // Default to page 1 if not provided
        if ($query) {
            // Split the query into individual words
            $keywords = explode(' ', $query);

            // Initialize the query builder
            $queryBuilder = Book::query()->with('category', 'author');

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
            $books = Book::with('category', 'author')->paginate(6, ['*'], 'book_page', $page);
        }

        return response()->json($books);
    }

}
