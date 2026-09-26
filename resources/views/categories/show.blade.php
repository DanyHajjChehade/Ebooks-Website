{{-- Category shelf (DESIGN.md §5.1 categories.show). --}}
@php $total = $books->total(); @endphp
<x-layouts.app :title="$category->name" :description="\Illuminate\Support\Str::limit(trim((string) $category->description) ?: $category->name.' ebooks, ready to download as EPUB and PDF.', 155)" :canonical="$books->currentPage() > 1 ? route('categories.show', [$category, 'page' => $books->currentPage()]) : route('categories.show', $category)">
    <div class="container-page py-section-sm">
        <x-breadcrumbs class="mb-6" :items="[['Books', route('books.index')], [$category->name]]"/>
        <header class="mb-10 grid gap-3">
            <p class="eyebrow">Shelf</p>
            <h1 class="h1">{{ $category->name }}</h1>
            @if ($category->description)<p class="lede">{{ $category->description }}</p>@endif
            <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted">
                <span>{{ number_format($total) }} {{ \Illuminate\Support\Str::plural('book', $total) }}</span>
                <a class="link inline-flex min-h-11 items-center gap-1 font-semibold" href="{{ route('books.index', ['category' => $category->slug]) }}">Search all books<x-icon name="arrow-right" class="icon-sm"/></a>
            </p>
        </header>

        @if ($books->isEmpty())
            <x-empty-state heading="This shelf is empty for now.">
                <x-slot:actions><x-button variant="primary" :href="route('books.index')">Browse all books</x-button></x-slot:actions>
            </x-empty-state>
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    <x-book-card :book="$book" :level="2"/>
                @endforeach
            </div>
            <x-pagination class="mt-12" :paginator="$books"/>
        @endif
    </div>
</x-layouts.app>
