<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request): View
    {
        $filters = $request->filters();

        $orders = Order::query()
            ->with('user:id,name,email')
            ->withCount('items')
            ->when($filters['status'], fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['q'], function (Builder $query, string $term) {
                // Accepts an email fragment, an order id ("42") or a reference ("BP-000042").
                $id = preg_match('/^(?:BP-)?0*(\d+)$/i', $term, $m) ? (int) $m[1] : null;
                $like = '%'.str_replace(['%', '_'], ' ', $term).'%';

                $query->where(fn (Builder $q) => $q
                    ->when($id, fn (Builder $q) => $q->orWhereKey($id))
                    ->orWhere('stripe_checkout_session_id', $term)
                    ->orWhere('stripe_payment_intent_id', $term)
                    ->orWhereHas('user', fn (Builder $u) => $u->whereLike('email', $like)->orWhereLike('name', $like)));
            })
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['user', 'items.book.author']),
        ]);
    }
}
