{{-- Admin dashboard (DESIGN.md §5.4): stats, recent orders, best sellers. --}}
@php $money = fn (int $c, ?string $cur = null) => $c === 0 ? 'Free' : \App\Support\Money::format($c, $cur); @endphp
<x-layouts.admin title="Dashboard">
    <x-admin.page-header title="Dashboard"/>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Revenue · 30 days" :value="\App\Support\Money::format($stats['revenue_cents_30d'])" hint="Paid orders only"/>
        <x-stat-card label="Paid orders · 30 days" :value="number_format($stats['paid_orders_30d'])" hint="Excludes refunds"/>
        <x-stat-card label="Customers" :value="number_format($stats['customers'])" hint="Accounts that aren’t admins"/>
        <x-stat-card label="Published books" :value="number_format($stats['published_books'])" hint="Visible in the shop"/>
    </div>

    <div class="grid grid-cols-1 gap-6 min-[90rem]:grid-cols-12 min-[90rem]:gap-8">
        <section class="grid content-start gap-4 min-[90rem]:col-span-8" aria-labelledby="recent-h">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="h4" id="recent-h">Recent orders</h2>
                <a class="link inline-flex min-h-11 items-center gap-1 text-sm font-semibold" href="{{ route('admin.orders.index') }}">View all orders<x-icon name="arrow-right" class="icon-sm"/></a>
            </div>
            @if ($recentOrders->isEmpty())
                <div class="card p-6 text-muted">No orders yet. They’ll show up here as soon as someone buys a book.</div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <caption class="sr-only">Recent orders</caption>
                        <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col" class="num">Total</th><th scope="col">Status</th><th scope="col">Date</th></tr></thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
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
                                    <td class="num">{{ $money((int) $order->subtotal_cents, $order->currency) }}</td>
                                    <td><x-order-status :status="$order->status"/></td>
                                    <td class="whitespace-nowrap"><time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M j, Y') }}</time></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="grid content-start gap-4 min-[90rem]:col-span-4" aria-labelledby="best-h">
            <h2 class="h4" id="best-h">Best sellers</h2>
            @if ($topBooks->isEmpty())
                <div class="card p-6 text-muted">No sales yet.</div>
            @else
                <ol class="card divide-y divide-line">
                    @foreach ($topBooks as $book)
                        <li class="flex items-center gap-3 p-3">
                            <span class="w-6 flex-none text-center font-serif text-xl italic text-muted" aria-hidden="true">{{ $loop->iteration }}</span>
                            <x-book-cover class="w-10 flex-none" :book="$book"/>
                            <div class="grid min-w-0 flex-1">
                                @if ($book->trashed())
                                    <span class="truncate font-semibold">{{ $book->title }}</span>
                                @else
                                    <a class="truncate font-semibold link-quiet" href="{{ route('admin.books.edit', $book) }}">{{ $book->title }}</a>
                                @endif
                                <span class="text-sm text-muted">{{ $book->sales_count }} sold · {{ \App\Support\Money::format((int) $book->revenue_cents) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</x-layouts.admin>
