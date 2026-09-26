{{-- Your orders (DESIGN.md §5.3): table from md, stacked link cards on phones. --}}
@use('Illuminate\Support\Str')
@php
    $titles = function ($order) {
        $t = $order->items->pluck('title');
        return $t->take(2)->implode(', ').($t->count() > 2 ? ' +'.($t->count() - 2).' more' : '');
    };
@endphp
<x-layouts.app title="Orders" :noindex="true">
    <div class="container-page py-section-sm">
        <x-account-header title="Orders" :subtitle="$orders->total() ? $orders->total().' '.Str::plural('order', $orders->total()) : null"/>

        @if ($orders->isEmpty())
            <x-empty-state heading="No orders yet" icon="receipt-text">
                When you buy a book, your receipt appears here.
                <x-slot:actions><x-button variant="primary" :href="route('books.index')" trailing="arrow-right">Browse the books</x-button></x-slot:actions>
            </x-empty-state>
        @else
            <div class="table-wrap hidden md:block">
                <table class="table">
                    <caption class="sr-only">Your orders</caption>
                    <thead>
                        <tr><th scope="col">Order</th><th scope="col">Date</th><th scope="col">Books</th><th scope="col" class="num">Total</th><th scope="col">Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <th scope="row"><a class="link" href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a></th>
                                <td><time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M j, Y') }}</time></td>
                                <td class="max-w-xs truncate">{{ $titles($order) }}</td>
                                <td class="num">{{ $order->subtotal_cents === 0 ? 'Free' : \App\Support\Money::format($order->subtotal_cents, $order->currency) }}</td>
                                <td><x-order-status :status="$order->status"/></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="grid grid-cols-1 gap-3 md:hidden">
                @foreach ($orders as $order)
                    <li class="card relative grid gap-2 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <a class="font-semibold text-ink no-underline after:absolute after:inset-0" href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a>
                            <x-order-status :status="$order->status"/>
                        </div>
                        <time class="text-sm text-muted" datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M j, Y') }}</time>
                        <p class="line-clamp-2 text-sm">{{ $titles($order) }}</p>
                        <p class="text-right font-semibold">{{ $order->subtotal_cents === 0 ? 'Free' : \App\Support\Money::format($order->subtotal_cents, $order->currency) }}</p>
                    </li>
                @endforeach
            </ul>

            <x-pagination class="mt-8" :paginator="$orders"/>
        @endif
    </div>
</x-layouts.app>
