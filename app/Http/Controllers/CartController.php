<?php

namespace App\Http\Controllers;

use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Book;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart=Cart::content();
        return view('components.MainViews.cart',['cart'=>$cart]);
    }

    public function store(Request $request)
    {
        $book=Book::findOrFail($request->input('book_id'));
        if($book->discount_price)
        {
            $price=$book->discount_price;
        }
        else
        {
            $price=$book->price;
        }
        Cart::add([
            'id'=>$book->id,
            'name'=>$book->title,
            'price'=>$price,
            'qty'=>$request->input('quantity'),
            'weight' =>0,
            'options' => ['copies_owned' =>$book->copies_owned ]]);

            session()->flash('book-success-' . $book->id, 'Book added to cart successfully.');
            return redirect()->back();
    }
    public function remove($rowId)
    {
        Cart::remove($rowId);

        return redirect()->back()->with('success', 'Book removed from cart!');
    }
}
