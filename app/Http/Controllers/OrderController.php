<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => $request->user()->orders()
                ->with('items')
                ->latest()
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load('items.book.author'),
        ]);
    }
}
