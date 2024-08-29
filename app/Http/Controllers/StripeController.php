<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderDetails;
use Stripe\Exception\ApiConnectionException;


class StripeController extends Controller
{
    public function index()
    {
        $cart=Cart::content();
        return view('components.MainViews.Checkout',['cart'=>$cart]);
    }

    public function checkout()
    {

        $cart=Cart::content();

    $lineItems = [];
    $puchasedbooks=[];
// Loop through each item in the cart
    foreach ($cart as $cartitem) {
    // Calculate the total price for each book item
     $booktotal=$cartitem->price*100;
    // Add a line item for the current book item
    $lineItems[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $cartitem->name, // Use the name of the book item
            ],
            'unit_amount_decimal' => $booktotal, // Use the total price for the book item
        ],
        'quantity' => $cartitem->qty, // Assuming one of each book item
    ];
}
$purchasedBooks[$cartitem->id] = $cartitem->qty;
session(['purchased_books' => $purchasedBooks]);
try {
\Stripe\Stripe::setApiKey(config('stripe.sk'));
$session = \Stripe\Checkout\Session::create([
    'line_items' => $lineItems, // Use the array of line items
    'mode' => 'payment',
    'success_url' =>route('payment.success',), // Change to your success URL
    'cancel_url' =>route('payment.index'), // Change to your cancel URL
]);
    return redirect()->away($session->url);
} catch (ApiConnectionException $e) {
    throw $e; // This will be caught by the custom exception handler
}
    }

    public function success(Request $request)
    {

        $puchasedbooks=$request->session()->get('purchased_books');

        if ($puchasedbooks) {
        foreach ($puchasedbooks as $bookId => $qty) {
            $book = Book::find($bookId);
            if ($book) {
                // Ensure stock quantity doesn't go negative
                $newStockQuantity = max(0, $book->copies_owned - $qty);
                $book->copies_owned = $newStockQuantity;
                $book->save();

            }
        }
    }
    else
    {
        return redirect()->route('index');
    }
    $user=Auth::user();
    $totalprice=Cart::subtotal();
    $order=Order::create([
        'user_id'=>$user->id,
        'payment_method'=>'Stripe',
        'total_price'=>$totalprice,
        'address'=>$user->address,
    ]);
    // Create order items

    foreach ($puchasedbooks as $bookId => $qty) {
        $book = Book::find($bookId);
        if ($book) {
            if($book->discount_price)
            {
                $price=$book->discount_price;
            }
            else
            {
                $price=$book->price;
            }
            OrderDetails::create([
                'order_id' => $order->id,
                'book_id' => $book->id,
                'quantity' => $qty,
                'price' => $price
            ]);
        }
    }
    Cart::destroy();
    session()->flash('purchased', 'Your purchase is done successfully');
    // Clear the purchased books information from the session
        $request->session()->forget('purchased_books');

        return view('components.MainViews.Checkout');

    }
}
