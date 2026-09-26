<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Models\Order;
use App\Payments\PaymentGateway;
use App\Payments\PaymentGatewayException;
use App\Services\CheckoutService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
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

    /**
     * Refund a paid order in full through the payment gateway and revoke the
     * library access it granted. Safe to repeat and to race with the
     * `charge.refunded` webhook.
     *
     * Flash keys: `status` on success; `error` = the payment provider's reason
     * (the view titles it "Stripe couldn’t refund this order"); `refund_blocked`
     * when the order isn't refundable at all (not paid).
     */
    public function refund(Order $order, PaymentGateway $gateway, CheckoutService $checkout): RedirectResponse
    {
        if (! $order->isPaid()) {
            return back()->with('refund_blocked', 'Only paid orders can be refunded.');
        }

        if ($order->subtotal_cents > 0) {
            try {
                $gateway->refund($order);
            } catch (PaymentGatewayException $e) {
                report($e);

                return back()->with('error', Str::finish(rtrim($e->getMessage()), '.'));
            }
        }

        $removed = $checkout->refund($order) ?? 0;
        $books = $removed === 1 ? '1 book' : "{$removed} books";

        return redirect()->route('admin.orders.show', $order)
            ->with('status', "Order #{$order->getKey()} refunded. {$books} removed from the customer’s library.");
    }
}
