{{-- Your library (DESIGN.md §5.3): owned books incl. soft-deleted/unpublished ones (A2). No cover flags here. --}}
@use('Illuminate\Support\Number')
@use('Illuminate\Support\Str')
<x-layouts.app title="Your library" :noindex="true">
    <div class="container-page py-section-sm">
        <x-account-header title="Your library" :subtitle="$books->count().' '.Str::plural('book', $books->count())"/>

        @if ($books->isEmpty())
            <x-empty-state heading="Your library is empty">
                Books you buy appear here, ready to download as EPUB or PDF.
                <x-slot:actions><x-button variant="primary" :href="route('books.index')" trailing="arrow-right">Browse the books</x-button></x-slot:actions>
            </x-empty-state>
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    @php
                        $live = ! $book->trashed() && $book->isPubliclyVisible();
                        $format = strtoupper((string) $book->file_format);
                        $size = Number::fileSize((int) $book->file_size, maxPrecision: 1);
                    @endphp
                    <article class="book-card">
                        <x-book-cover :book="$book"/>
                        <div class="book-card__body">
                            <h2 class="book-card__title">
                                @if ($live)<a class="book-card__link" href="{{ route('books.show', $book) }}">{{ $book->title }}</a>@else{{ $book->title }}@endif
                            </h2>
                            <p class="book-card__author">{{ $book->author?->name }}</p>
                            @unless ($live)<span class="badge mt-1 self-start">No longer sold</span>@endunless
                            <div class="mt-auto grid gap-1 pt-2">
                                <a class="btn btn-secondary btn-sm btn-block book-card__action" href="{{ route('library.download', $book) }}" aria-label="Download {{ $book->title }}, {{ $format }}, {{ $size }}"><x-icon name="download"/>Download {{ $format }}</a>
                                @if ($live)
                                    <a class="btn btn-ghost btn-sm btn-block book-card__action" href="{{ route('books.show', $book) }}#reviews">Write a review</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
