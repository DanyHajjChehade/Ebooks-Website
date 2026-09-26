{{--
    Buy / in-cart / owned call to action (DESIGN.md §5.1 books.show, A9).
    variant: page (book page + home spotlight) | bar (mobile sticky buy bar, one small button).
    group: hosts with the same group swap together to "in cart" after a fetch add (cart.js).
    Slot `extra` adds a secondary link to the default state (e.g. "Read more" on home).
--}}
@props(['book', 'owned' => false, 'inCart' => false, 'variant' => 'page', 'group' => 'book', 'extra' => null])
@php
    $format = strtoupper((string) $book->file_format);
    $size = \Illuminate\Support\Number::fileSize((int) $book->file_size, maxPrecision: 1);
    $bar = $variant === 'bar';
@endphp
<div {{ $attributes->class($bar ? 'flex-none' : 'grid justify-items-start gap-3 max-sm:justify-items-stretch') }} data-cta-swap="{{ $group }}">
    @if ($owned)
        @unless ($bar)<span class="badge badge-success justify-self-start"><x-icon name="check"/>In your library</span>@endunless
        <div class="flex flex-wrap items-center gap-3">
            <a class="btn btn-primary {{ $bar ? '' : 'btn-lg max-sm:w-full' }}" href="{{ route('library.download', $book) }}" aria-label="Download {{ $format }}, {{ $size }}"><x-icon name="download"/>{{ $bar ? 'Download' : "Download {$format} · {$size}" }}</a>
            @unless ($bar)<a class="btn btn-ghost btn-lg max-sm:w-full" href="{{ route('library.index') }}">Go to your library</a>@endunless
        </div>
    @elseif ($inCart)
        @include('components.partials.cta-in-cart', ['bar' => $bar])
    @else
        <div class="flex flex-wrap items-center gap-3">
            <form class="{{ $bar ? '' : 'max-sm:w-full' }}" method="POST" action="{{ route('cart.store') }}" data-add-to-cart data-title="{{ $book->title }}">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <button class="btn btn-primary {{ $bar ? '' : 'btn-lg min-w-64 max-sm:w-full' }}" type="submit" data-busy-label="Adding…"><x-icon name="shopping-bag"/>Add to cart</button>
            </form>
            {{ $extra }}
        </div>
        <template data-in-cart>@include('components.partials.cta-in-cart', ['bar' => $bar])</template>
    @endif
</div>
