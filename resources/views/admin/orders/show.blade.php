{{-- Admin order (DESIGN.md §5.4): items, customer, payment ids, timeline, Refund (paid only, A4). --}}
@php
    $money = fn (int $c) => $c === 0 ? 'Free' : \App\Support\Money::format($c, $order->currency);
    $total = $money((int) $order->subtotal_cents);
    $n = $order->items->count();
    $status = $order->status->value;
    $refundBody = $order->subtotal_cents > 0
        ? 'Stripe will return '.$total.' to the customer’s card, and '.$n.' '.\Illuminate\Support\Str::plural('book', $n).' will leave their library. This can’t be undone.'
        : $n.' '.\Illuminate\Support\Str::plural('book', $n).' will leave the customer’s library. This can’t be undone.';
    $time = fn ($d) => $d?->format('M j, Y, g:i A');
@endphp
<x-layouts.admin :title="'Order '.$order->reference" :flash-except="['error']">
    <x-admin.page-header :title="'Order '.$order->reference" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Orders', route('admin.orders.index')], [$order->reference]]">
        <x-slot:actions>
            <x-order-status :status="$order->status"/>
            @if ($order->isPaid())
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}">
                    @csrf
                    <button class="btn btn-danger-quiet" type="submit" data-confirm="Refund order {{ $order->reference }}?" data-confirm-body="{{ $refundBody }}"
                        data-confirm-ok="{{ $order->subtotal_cents > 0 ? 'Refund '.$total : 'Refund order' }}" data-confirm-cancel="Keep order" data-confirm-busy="Refunding…">Refund order</button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('error'))
        <x-alert variant="danger" title="Stripe couldn’t refund this order">{{ session('error') }} Nothing was changed.</x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 xl:gap-8">
        <section class="grid content-start gap-4 xl:col-span-8" aria-labelledby="items-h">
            <h2 class="h4" id="items-h">Books</h2>
            <div class="table-wrap">
                <table class="table">
                    <caption class="sr-only">Books in this order</caption>
                    <thead><tr><th scope="col">Book</th><th scope="col" class="num">Price paid</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <th scope="row">
                                    <span class="flex items-center gap-3">
                                        @if ($item->book)<x-book-cover class="w-10 flex-none" :book="$item->book"/>@endif
                                        <span class="grid min-w-0">
                                            @if ($item->book && ! $item->book->trashed())
                                                <a class="link-quiet font-semibold" href="{{ route('admin.books.edit', $item->book) }}">{{ $item->title }}</a>
                                            @else
                                                <span class="font-semibold">{{ $item->title }}</span>
                                            @endif
                                            @if ($item->book?->author)<span class="font-normal text-muted">{{ $item->book->author->name }}</span>@endif
                                        </span>
                                    </span>
                                </th>
                                <td class="num">{{ $money((int) $item->price_cents) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th scope="row" class="text-right">Total</th><td class="num font-semibold">{{ $total }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <div class="grid content-start gap-6 xl:col-span-4">
            <section class="card grid gap-3 p-5" aria-labelledby="customer-h">
                <h2 class="h4" id="customer-h">Customer</h2>
                @if ($order->user)
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$order->user->name" :id="$order->user->id"/>
                        <div class="grid min-w-0">
                            <span class="truncate font-semibold">{{ $order->user->name }}</span>
                            <a class="link truncate text-sm" href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a>
                        </div>
                    </div>
                @else
                    <p class="italic text-muted">Deleted account</p>
                @endif
            </section>

            <section class="card grid gap-3 p-5" aria-labelledby="payment-h">
                <h2 class="h4" id="payment-h">Payment</h2>
                <dl class="grid gap-3 text-sm">
                    @foreach (['Checkout session' => $order->stripe_checkout_session_id, 'Payment intent' => $order->stripe_payment_intent_id] as $label => $value)
                        <div class="grid gap-1">
                            <dt class="text-muted">{{ $label }}</dt>
                            <dd class="flex items-center gap-1">
                                @if ($value)
                                    <code class="min-w-0 flex-1 truncate font-mono text-xs">{{ $value }}</code>
                                    <button class="btn btn-ghost btn-sm btn-icon js-only" type="button" data-copy="{{ $value }}" aria-label="Copy {{ strtolower($label) }} id"><x-icon name="copy"/><span class="sr-only" data-copy-label>Copy</span></button>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                    <div class="grid gap-1"><dt class="text-muted">Currency</dt><dd>{{ strtoupper($order->currency) }}</dd></div>
                </dl>
            </section>

            <section class="card grid gap-3 p-5" aria-labelledby="timeline-h">
                <h2 class="h4" id="timeline-h">Timeline</h2>
                <ol class="timeline text-sm">
                    <li data-state="done"><span class="block font-semibold">Created</span><time class="text-muted" datetime="{{ $order->created_at->toIso8601String() }}">{{ $time($order->created_at) }}</time></li>
                    @if ($order->paid_at)
                        <li data-state="done"><span class="block font-semibold">Paid</span><time class="text-muted" datetime="{{ $order->paid_at->toIso8601String() }}">{{ $time($order->paid_at) }}</time></li>
                    @elseif ($status === 'pending')
                        <li><span class="block font-semibold">Waiting for payment</span></li>
                    @endif
                    @if ($status === 'refunded')
                        <li data-state="danger"><span class="block font-semibold">Refunded</span><time class="text-muted" datetime="{{ $order->updated_at->toIso8601String() }}">{{ $time($order->updated_at) }}</time></li>
                    @elseif ($status === 'failed')
                        <li data-state="danger"><span class="block font-semibold">Failed</span><time class="text-muted" datetime="{{ $order->updated_at->toIso8601String() }}">{{ $time($order->updated_at) }}</time></li>
                    @endif
                </ol>
            </section>
        </div>
    </div>
</x-layouts.admin>
