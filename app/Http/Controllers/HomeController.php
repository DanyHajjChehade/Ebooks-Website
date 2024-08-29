<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Author;
use App\Models\Setting;
use App\Models\Book;
use App\Models\Category;
use App\Models\Comment;
use Gloudemans\Shoppingcart\Facades\Cart;

class HomeController extends Controller
{
    public function index()
    {
        $cart=Cart::content();
        $authors = Author::paginate(4);
        $books=Book::paginate(6);
        $comments=Comment::paginate(6);
        $categories=Category::all();
        return view('index', [
        'authors' => $authors,
        'books'=>$books,
        'categories'=>$categories,
        'comments'=>$comments,
        'cart'=>$cart]);
    }
}
