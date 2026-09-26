{{-- Author page (DESIGN.md §5.1 authors.show): portrait, bio, their books. --}}
@php
    $total = $books->total();
    // preg_split returns false on invalid UTF-8: fall back to one paragraph.
    $bio = collect(preg_split('/\R\s*\R/u', trim((string) $author->bio)) ?: [trim((string) $author->bio)])->map(fn ($p) => trim($p))->filter();
@endphp
<x-layouts.app :title="$author->name" :description="\Illuminate\Support\Str::limit(trim((string) $author->bio) ?: 'Ebooks by '.$author->name.', ready to download as EPUB and PDF.', 155)">
    <div class="container-page py-section-sm">
        <x-breadcrumbs class="mb-6" :items="[['Authors', route('authors.index')], [$author->name]]"/>
        <header class="grid grid-cols-1 items-start gap-6 sm:grid-cols-[auto_minmax(0,1fr)] sm:gap-10">
            <x-avatar class="[--size:7rem] sm:[--size:10rem]" :name="$author->name" :id="$author->id" size="xl" :photo="$author->photo_url"/>
            <div class="grid gap-3">
                <p class="eyebrow">Author</p>
                <h1 class="h1">{{ $author->name }}</h1>
                <p class="text-muted">{{ number_format($total) }} {{ \Illuminate\Support\Str::plural('book', $total) }}</p>
                @if ($bio->isNotEmpty())
                    <div class="prose-book mt-2">
                        @foreach ($bio as $p)<p>{{ $p }}</p>@endforeach
                    </div>
                @endif
            </div>
        </header>

        <section class="mt-section-sm border-t border-line pt-section-sm" aria-labelledby="books-h">
            <h2 class="h2 mb-8" id="books-h">Books by {{ $author->name }}</h2>
            @if ($books->isEmpty())
                <x-empty-state :heading="'No books from '.$author->name.' yet.'" :level="3">
                    <x-slot:actions><x-button variant="primary" :href="route('books.index')">Browse all books</x-button></x-slot:actions>
                </x-empty-state>
            @else
                <div class="book-grid">
                    @foreach ($books as $book)
                        <x-book-card :book="$book"/>
                    @endforeach
                </div>
                <x-pagination class="mt-12" :paginator="$books"/>
            @endif
        </section>
    </div>
</x-layouts.app>
