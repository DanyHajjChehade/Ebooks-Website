{{--
    Book card (DESIGN.md §4.7). States from the shared $ownedBookIds / $cartBookIds (A9):
    default · on sale · free · in cart (flag + "View cart") · owned (flag + Download).
    :level sets the title heading level (2 on listing pages that have no h2, 3 under a section h2).
--}}
@props(['book', 'level' => 3])
@php
    $owned = in_array((int) $book->id, $ownedBookIds ?? [], true);
    $inCart = ! $owned && in_array((int) $book->id, $cartBookIds ?? [], true);
    $format = strtoupper((string) $book->file_format);
    $size = \Illuminate\Support\Number::fileSize((int) $book->file_size, maxPrecision: 1);
    $tag = 'h'.max(2, min(4, (int) $level));
@endphp
<article {{ $attributes->class('book-card') }} data-book-card>
    <x-book-cover :book="$book">
        @if ($owned)
            <span class="cover-flag cover-flag--owned"><x-icon name="check"/>In your library</span>
        @elseif ($inCart)
            <span class="cover-flag"><x-icon name="shopping-bag"/>In cart</span>
        @endif
    </x-book-cover>
    <div class="book-card__body">
        <{{ $tag }} class="book-card__title"><a class="book-card__link" href="{{ route('books.show', $book) }}">{{ $book->title }}</a></{{ $tag }}>
        <p class="book-card__author">{{ $book->author?->name }}</p>
        <div class="book-card__meta" data-card-meta>
            @if ($owned)
                <a class="btn btn-secondary btn-sm btn-block book-card__action" href="{{ route('library.download', $book) }}" aria-label="Download {{ $book->title }}, {{ $format }}, {{ $size }}"><x-icon name="download"/>Download</a>
            @elseif ($inCart)
                <x-price :book="$book"/>
                <a class="btn btn-ghost btn-sm book-card__action" href="{{ route('cart.index') }}">View cart</a>
            @else
                <x-price :book="$book"/>
                <form class="book-card__action" method="POST" action="{{ route('cart.store') }}" data-add-to-cart data-title="{{ $book->title }}">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <button class="btn btn-secondary btn-sm" type="submit" aria-label="Add {{ $book->title }} to cart" data-busy-label="Adding"><x-icon name="plus"/>Add</button>
                </form>
            @endif
        </div>
    </div>
</article>
