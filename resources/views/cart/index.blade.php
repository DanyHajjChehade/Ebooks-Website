{{-- Cart (DESIGN.md §5.1 cart.index): rows, sticky summary, checkout CTA + consent line (A5). --}}
@use('Illuminate\Support\Number')
@use('Illuminate\Support\Str')
@php
    $n = $items->count();
    $books = fn (int $n) => $n.' '.Str::plural('book', $n);
    $total = \App\Support\Money::format($subtotalCents);
    $totalLabel = $subtotalCents === 0 ? 'Free' : $total;
@endphp
<x-layouts.app title="Your cart" :noindex="true" :flash-except="['error']">
    <div class="container-page py-section-sm">
        <header class="mb-8 grid gap-1">
            <h1 class="h1">Your cart</h1>
            <p class="text-muted" data-cart-books-count>{{ $books($n) }}</p>
        </header>

        @if (session('error'))
            <x-alert class="mb-8 max-w-prose" variant="danger">{{ session('error') }}</x-alert>
        @endif

        <div data-cart-body>
            @if ($items->isEmpty())
                @include('cart.partials.empty')
            @else
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-8">
                    <ul class="border-t border-line lg:col-span-7" aria-label="Books in your cart">
                        @foreach ($items as $book)
                            <li class="flex gap-4 overflow-hidden border-b border-line py-5" data-cart-row>
                                <a class="w-16 flex-none" href="{{ route('books.show', $book) }}" tabindex="-1" aria-hidden="true"><x-book-cover :book="$book"/></a>
                                <div class="grid min-w-0 flex-1 content-start gap-1">
                                    <a class="font-serif text-lg leading-tight link-quiet" href="{{ route('books.show', $book) }}">{{ $book->title }}</a>
                                    <p class="text-sm text-muted">{{ $book->author?->name }} · {{ strtoupper((string) $book->file_format) }} · {{ Number::fileSize((int) $book->file_size, maxPrecision: 1) }}</p>
                                    <form method="POST" action="{{ route('cart.destroy', $book->id) }}" data-cart-remove data-title="{{ $book->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-ghost btn-sm -ml-3" type="submit" aria-label="Remove {{ $book->title }} from your cart"><x-icon name="trash-2"/>Remove</button>
                                    </form>
                                </div>
                                <x-price class="flex-col items-end gap-0 text-right" :book="$book"/>
                            </li>
                        @endforeach
                    </ul>

                    <aside class="lg:col-span-4 lg:col-start-9" aria-labelledby="summary-h">
                        <div class="card grid gap-5 p-6 lg:sticky lg:top-[calc(var(--spacing-header)+1.5rem)]" data-cart-summary data-currency="{{ \App\Support\Money::currency() }}" data-locale="{{ app()->getLocale() }}">
                            <h2 class="h4" id="summary-h">Order summary</h2>
                            <dl class="grid gap-3 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted">Subtotal · <span data-cart-books-count>{{ $books($n) }}</span></dt>
                                    <dd data-subtotal>{{ $totalLabel }}</dd>
                                </div>
                                @if (config('services.stripe.automatic_tax'))
                                    <div class="flex justify-between gap-4"><dt class="text-muted">Tax</dt><dd class="text-muted">Calculated at checkout</dd></div>
                                @endif
                                <div class="flex items-baseline justify-between gap-4 border-t border-line pt-3">
                                    <dt class="font-semibold">Total</dt>
                                    <dd class="price text-xl" data-subtotal>{{ $totalLabel }}</dd>
                                </div>
                            </dl>
                            @auth
                                <form method="POST" action="{{ route('checkout.store') }}">
                                    @csrf
                                    <button class="btn btn-primary btn-lg btn-block" type="submit" data-busy-label="Opening secure checkout…"><x-icon name="lock"/><span data-checkout-label data-free-label="Get your books">{{ $subtotalCents === 0 ? 'Get your books' : 'Check out' }}</span></button>
                                </form>
                            @else
                                <div class="grid gap-2">
                                    <a class="btn btn-primary btn-lg btn-block" href="{{ route('login', ['return' => 'cart']) }}"><x-icon name="lock"/>Log in to check out</a>
                                    <p class="text-center text-sm">New here? <a class="link" href="{{ route('register', ['return' => 'cart']) }}">Create an account</a></p>
                                </div>
                            @endauth
                            <p class="text-xs text-muted">Ebooks are digital content, ready to download as soon as your payment clears. By selecting Check out, you ask for immediate access and accept that you can’t cancel for a refund once the download is available. <a class="link" href="{{ route('pages.refunds') }}">Refund policy</a></p>
                            <p class="text-xs text-muted">Payments are processed by Stripe. We never see your card number.</p>
                            <a class="btn btn-ghost" href="{{ route('books.index') }}">Continue browsing</a>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
        <template data-cart-empty>@include('cart.partials.empty')</template>
    </div>
</x-layouts.app>
