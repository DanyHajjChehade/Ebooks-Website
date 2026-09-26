{{-- PLACEHOLDER partial: $list (iterable of Book with author loaded) --}}
<ul>
    @forelse ($list as $b)
        <li>
            <a href="{{ route('books.show', $b) }}">{{ $b->title }}</a>
            by {{ $b->author->name }} —
            @if ($b->isOnSale()) <s>@money($b->price_cents)</s> @endif
            {{ $b->isFree() ? 'Free' : \App\Support\Money::format($b->effective_price_cents) }}
            @isset($b->reviews_avg_rating) ({{ number_format((float) $b->reviews_avg_rating, 1) }}★) @endisset
            @if (in_array($b->id, $ownedBookIds, true)) [In your library] @elseif (in_array($b->id, $cartBookIds, true)) [In cart] @endif
        </li>
    @empty
        <li>No books found.</li>
    @endforelse
</ul>
