{{-- Checkout success (DESIGN.md §5.1): paid → "Your books are ready"; otherwise the processing variant. --}}
@use('Illuminate\Support\Number')
@php
    $paid = $order->isPaid();
    $failed = $order->status->value === 'failed';
    $refunded = $order->status->value === 'refunded';
@endphp
<x-layouts.app :title="$paid ? 'Your books are ready' : ($refunded ? 'Order refunded' : ($failed ? 'Payment didn’t go through' : 'Payment processing'))" :noindex="true">
    <div class="container-page py-section-sm">
        <div class="mx-auto grid max-w-[36rem] grid-cols-1 gap-6">
            @if ($paid)
                <span class="inline-grid size-14 place-items-center rounded-full bg-success-soft text-success"><x-icon name="check" class="size-7"/></span>
                <div class="grid gap-3">
                    <p class="eyebrow">Order {{ $order->reference }} · Paid</p>
                    <h1 class="h1">Your books are ready.</h1>
                    <p class="lede">They’re in your library now. Download them here or any time later.</p>
                </div>
                <ul class="card divide-y divide-line" aria-label="Books in this order">
                    @foreach ($order->items as $item)
                        <li class="flex items-center gap-4 p-4">
                            @if ($item->book)
                                <x-book-cover class="w-12 flex-none" :book="$item->book"/>
                            @endif
                            <div class="grid min-w-0 flex-1 gap-0.5">
                                <p class="font-serif text-lg leading-tight">{{ $item->title }}</p>
                                @if ($item->book)
                                    <p class="text-sm text-muted">{{ strtoupper((string) $item->book->file_format) }} · {{ Number::fileSize((int) $item->book->file_size, maxPrecision: 1) }}</p>
                                @endif
                            </div>
                            @if ($item->book)
                                <a class="btn btn-secondary btn-sm flex-none" href="{{ route('library.download', $item->book) }}" aria-label="Download {{ $item->title }}, {{ strtoupper((string) $item->book->file_format) }}, {{ Number::fileSize((int) $item->book->file_size, maxPrecision: 1) }}"><x-icon name="download"/>Download</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="flex flex-wrap gap-3">
                    <x-button variant="primary" :href="route('library.index')">Go to your library</x-button>
                    <x-button variant="ghost" :href="route('books.index')">Keep browsing</x-button>
                </div>
                <p class="text-sm text-muted">Questions about this order? <a class="link" href="{{ route('pages.contact') }}">Contact us</a> and quote {{ $order->reference }}.</p>
            @elseif ($refunded)
                <span class="inline-grid size-14 place-items-center rounded-full bg-sunken text-muted"><x-icon name="receipt-text" class="size-7"/></span>
                <div class="grid gap-3">
                    <p class="eyebrow">Order {{ $order->reference }} · Refunded</p>
                    <h1 class="h1">This order was refunded.</h1>
                    <p class="lede">Refunded. These books were removed from your library.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-button variant="primary" :href="route('orders.show', $order)">View order</x-button>
                    <x-button variant="ghost" :href="route('books.index')">Keep browsing</x-button>
                </div>
                <p class="text-sm text-muted">Questions about this order? <a class="link" href="{{ route('pages.contact') }}">Contact us</a> and quote {{ $order->reference }}.</p>
            @elseif ($failed)
                <span class="inline-grid size-14 place-items-center rounded-full bg-danger-soft text-danger"><x-icon name="circle-alert" class="size-7"/></span>
                <div class="grid gap-3">
                    <p class="eyebrow">Order {{ $order->reference }} · Failed</p>
                    <h1 class="h1">Payment didn’t go through.</h1>
                    <p class="lede">You weren’t charged. Add the books to your cart to try again.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-button variant="primary" :href="route('cart.index')">Go to your cart</x-button>
                    <x-button variant="ghost" :href="route('orders.show', $order)">View order</x-button>
                </div>
            @else
                <span class="inline-grid size-14 place-items-center rounded-full bg-warning-soft text-warning"><x-icon name="clock" class="size-7"/></span>
                <div class="grid gap-3">
                    <p class="eyebrow">Order {{ $order->reference }} · {{ $order->status->label() }}</p>
                    <h1 class="h1">Payment processing</h1>
                    <p class="lede">Your bank is still confirming the payment. We’ll add the books to your library as soon as it clears, usually within a few minutes.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-button variant="primary" :href="route('orders.show', $order)">View order</x-button>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
