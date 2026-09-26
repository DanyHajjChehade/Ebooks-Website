<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $paidSince = fn () => Order::query()
            ->where('status', OrderStatus::Paid->value)
            ->where('paid_at', '>=', now()->subDays(30));

        $paidItems = fn (Builder $q) => $q->whereHas('order', fn (Builder $o) => $o->where('status', OrderStatus::Paid->value));

        return view('admin.dashboard', [
            'stats' => [
                'revenue_cents_30d' => (int) $paidSince()->sum('subtotal_cents'),
                'paid_orders_30d' => $paidSince()->count(),
                'customers' => User::query()->where('is_admin', false)->count(),
                'published_books' => Book::query()->published()->count(),
            ],
            'recentOrders' => Order::query()
                ->with('user:id,name,email')
                ->withCount('items')
                ->latest()
                ->latest('id')
                ->take(8)
                ->get(),
            'topBooks' => Book::query()
                ->withTrashed()
                ->with('author')
                ->whereHas('orderItems', $paidItems)
                ->withCount(['orderItems as sales_count' => $paidItems])
                ->withSum(['orderItems as revenue_cents' => $paidItems], 'price_cents')
                ->orderByDesc('sales_count')
                ->orderBy('title')
                ->take(5)
                ->get(),
        ]);
    }
}
