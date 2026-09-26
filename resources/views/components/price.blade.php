{{--
    Price display. Either :book (uses its sale state) or :cents (+ optional :was for a struck original).
    Zero reads "Free". size="lg" for the book page.
--}}
@props(['book' => null, 'cents' => null, 'was' => null, 'size' => 'md', 'currency' => null])
@php
    if ($book) {
        $now = (int) $book->effective_price_cents;
        $was = $book->isOnSale() ? (int) $book->price_cents : null;
    } else {
        $now = (int) $cents;
        $was = ($was !== null && (int) $was > $now) ? (int) $was : null;
    }
    $onSale = $was !== null;
    $fmt = fn (int $c) => $c === 0 ? 'Free' : \App\Support\Money::format($c, $currency);
@endphp
<p {{ $attributes->class(['price', 'price--sale' => $onSale, 'price--lg' => $size === 'lg']) }}>@if ($onSale)<span class="sr-only">Sale price</span>@endif<span class="price__now">{{ $fmt($now) }}</span>@if ($onSale)<del class="price__was"><span class="sr-only">Original price</span>{{ $fmt($was) }}</del>@endif</p>
