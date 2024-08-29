<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Book;

class OrderController extends Controller
{
    public function index()
    {
        return view('dashboard.orders.index',['orders'=>Order::all()]);
    }

    public function cancel($orderid)
    {
        $order=Order::findOrFail($orderid);
        $order->update([
            'status'=>'Canceled'
        ]);
        $orderDetails = OrderDetails::where('order_id', $orderid)->get();

        foreach ($orderDetails as $detail) {
            $book = Book::find($detail->book_id);
            if ($book) {
                // Adjust the stock
                $book->copies_owned += $detail->quantity;
                $book->save();
            }
        }


        return redirect()->route('dashboard.orders.index');
    }

    public function deliver($orderid)
    {
        $order=Order::findOrFail($orderid);
        $order->update([
            'status'=>'Delivered'
        ]);
        return redirect()->route('dashboard.orders.index');
    }

    public function delete($orderid)
    {
        $order=Order::findOrFail($orderid);
        $order->delete();
        return redirect()->route('dashboard.orders.index');
    }
}
