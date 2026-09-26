{{--
    Book cover: the uploaded image, or the generated typographic cover (DESIGN.md §4.6).
    Generated covers: swatch (id × 7) % 10, layout id % 4, title size = max(by length, by longest word).
    No inline styles: the CSS reads data-swatch / data-layout / data-len.
    Props: :book (author loaded), :eager (hero/book page), :alt (book page only; cards use "").
    :author-name avoids touching the author relation (admin form preview).
    The default slot holds cover flags.
--}}
@props(['book', 'eager' => false, 'alt' => '', 'authorName' => null])
@php
    $coverUrl = $book->cover_path ? $book->cover_url : null;
    if (! $coverUrl) {
        $id = (int) $book->id;
        $swatch = ($id * 7) % 10;
        $layout = ['classic', 'frame', 'initial', 'orbit'][$id % 4];
        $t = (string) $book->title;
        $byLen = mb_strlen($t) <= 14 ? 0 : (mb_strlen($t) <= 32 ? 1 : (mb_strlen($t) <= 60 ? 2 : 3));
        $word = collect(preg_split('/[\s\-—]+/u', $t, -1, PREG_SPLIT_NO_EMPTY))->map(fn ($w) => mb_strlen($w))->max() ?? 0;
        $byWord = $word <= 8 ? 0 : ($word <= 12 ? 1 : ($word <= 16 ? 2 : 3));
        $len = ['s', 'm', 'l', 'xl'][max($byLen, $byWord)];
        $initial = mb_strtoupper(mb_substr(preg_replace('/^(the|a|an)\s+/iu', '', $t), 0, 1));
    }
@endphp
<div {{ $attributes->class('cover') }}>
    @if ($coverUrl)
        <img src="{{ $coverUrl }}" alt="{{ $alt }}" width="400" height="600" decoding="async" @if ($eager) loading="eager" fetchpriority="high" @else loading="lazy" @endif>
    @else
        <div class="gcover" data-swatch="{{ $swatch }}" data-layout="{{ $layout }}" aria-hidden="true">
            <span class="gcover__art"></span>
            <span class="gcover__initial">{{ $initial }}</span>
            <div class="gcover__inner">
                <span class="gcover__author">{{ $authorName ?? $book->author?->name }}</span>
                <span class="gcover__rule"></span>
                <span class="gcover__title" data-len="{{ $len }}">{{ $t }}</span>
                <svg class="gcover__mark" aria-hidden="true" focusable="false"><use href="#bp-planet"/></svg>
            </div>
        </div>
        @if ($alt)<span class="sr-only">{{ $alt }}</span>@endif
    @endif
    {{ $slot }}
</div>
