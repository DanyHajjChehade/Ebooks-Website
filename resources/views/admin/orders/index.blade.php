{{-- Admin orders (DESIGN.md §5.4): status tabs, search by email / order number / Stripe id. --}}
@php
    $status = $filters['status'] ?? null;
    $q = $filters['q'] ?? null;
    $tab = fn (?string $s) => route('admin.orders.index', array_filter(['status' => $s, 'q' => $q]));
@endphp
<x-layouts.admin title="Orders">
    <x-admin.page-header title="Orders" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Orders']]"/>
    <x-admin.status-tabs :tabs="[
        ['All', $tab(null), $status === null],
        ['Paid', $tab('paid'), $status === 'paid'],
        ['Pending', $tab('pending'), $status === 'pending'],
        ['Failed', $tab('failed'), $status === 'failed'],
        ['Refunded', $tab('refunded'), $status === 'refunded'],
    ]"/>
    <x-admin.filters :action="route('admin.orders.index')" label="Search orders" placeholder="Email or order number" :q="$q" :active="$q || $status">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    </x-admin.filters>

    @if ($orders->isEmpty())
        <x-empty-state :heading="$q || $status ? 'No orders match' : 'No orders yet'" icon="receipt-text">
            {{ $q || $status ? 'Try a different search or status.' : 'They’ll show up here as soon as someone buys a book.' }}
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <caption class="sr-only">Orders</caption>
                <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Date</th><th scope="col" class="num">Books</th><th scope="col" class="num">Total</th><th scope="col">Status</th></tr></thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <th scope="row"><a class="link" href="{{ route('admin.orders.show', $order) }}">{{ $order->reference }}</a></th>
                            <td>
                                @if ($order->user)
                                    <span class="block font-semibold">{{ $order->user->name }}</span>
                                    <span class="block text-muted">{{ $order->user->email }}</span>
                                @else
                                    <span class="italic text-muted">Deleted account</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap"><time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M j, Y') }}</time></td>
                            <td class="num">{{ $order->items_count }}</td>
                            <td class="num">{{ $order->subtotal_cents === 0 ? 'Free' : \App\Support\Money::format($order->subtotal_cents, $order->currency) }}</td>
                            <td><x-order-status :status="$order->status"/></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$orders"/>
    @endif
</x-layouts.admin>
