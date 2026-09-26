{{-- Order detail (DESIGN.md §5.3). Items are price snapshots; downloads show while the book is in the library. --}}
@use('Illuminate\Support\Number')
@php
    $status = $order->status->value;
    $money = fn (int $c) => $c === 0 ? 'Free' : \App\Support\Money::format($c, $order->currency);
@endphp
<x-layouts.app :title="'Order '.$order->reference" :noindex="true">
    <div class="container-page py-section-sm">
        <x-breadcrumbs class="mb-6" :items="[['Orders', route('orders.index')], [$order->reference]]"/>
        <header class="mb-8 grid gap-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="h1">Order {{ $order->reference }}</h1>
                <x-order-status :status="$order->status"/>
            </div>
            <p class="text-sm text-muted">
                Placed <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M j, Y, g:i A') }}</time>
                @if ($order->paid_at) · Paid <time datetime="{{ $order->paid_at->toIso8601String() }}">{{ $order->paid_at->format('M j, Y') }}</time>@endif
            </p>
        </header>

        <div class="grid max-w-3xl grid-cols-1 gap-8">
            @if ($status === 'pending')
                <x-alert variant="warning">Payment not confirmed yet. If you finished checkout, this updates within a few minutes.</x-alert>
            @elseif ($status === 'failed')
                <x-alert variant="danger">This payment didn’t go through, so you weren’t charged. Add the books to your cart to try again.</x-alert>
            @elseif ($status === 'refunded')
                <x-alert>Refunded. These books were removed from your library.</x-alert>
            @endif

            <section aria-labelledby="items-h">
                <h2 class="sr-only" id="items-h">Books</h2>
                <ul class="card divide-y divide-line">
                    @foreach ($order->items as $item)
                        @php $inLibrary = $item->book && in_array((int) $item->book_id, $ownedBookIds, true); @endphp
                        <li class="flex items-center gap-4 p-4">
                            @if ($item->book)<x-book-cover class="w-12 flex-none" :book="$item->book"/>@endif
                            <div class="grid min-w-0 flex-1 gap-0.5">
                                <p class="font-serif text-lg leading-tight">{{ $item->title }}</p>
                                @if ($item->book)
                                    <p class="text-sm text-muted">{{ strtoupper((string) $item->book->file_format) }} · {{ Number::fileSize((int) $item->book->file_size, maxPrecision: 1) }}</p>
                                @endif
                            </div>
                            <div class="grid flex-none justify-items-end gap-2">
                                <p class="font-semibold">{{ $money((int) $item->price_cents) }}</p>
                                @if ($order->isPaid() && $inLibrary)
                                    <a class="btn btn-secondary btn-sm" href="{{ route('library.download', $item->book) }}" aria-label="Download {{ $item->title }}"><x-icon name="download"/>Download</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="grid gap-3 text-sm sm:w-full sm:max-w-sm sm:justify-self-end">
                <dl class="grid gap-3">
                    <div class="flex justify-between gap-4"><dt class="text-muted">Subtotal</dt><dd class="tabular">{{ $money($order->subtotal_cents) }}</dd></div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-line pt-3"><dt class="font-semibold">Total</dt><dd class="tabular text-xl font-semibold">{{ $money($order->subtotal_cents) }}</dd></div>
                </dl>
                @if ($order->isPaid() && $order->subtotal_cents > 0)<p class="text-muted">Paid by card through Stripe</p>@endif
            </div>

            <p class="text-sm text-muted">Something wrong with this order? <a class="link" href="{{ route('pages.contact') }}">Contact us</a> and quote {{ $order->reference }}.</p>
        </div>
    </div>
</x-layouts.app>
