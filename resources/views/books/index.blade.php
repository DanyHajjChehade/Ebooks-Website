{{-- Catalogue (DESIGN.md §5.1 books.index): GET filter form, mobile filter sheet, chips, grid, pagination. --}}
@php
    $q = $filters['q'] ?? null;
    $sorts = ['newest' => 'Newest first', 'price_asc' => 'Price, low to high', 'price_desc' => 'Price, high to low', 'title' => 'Title, A to Z'];
    $categoryOptions = $categories->mapWithKeys(fn ($c) => [$c->slug => $c->name.' ('.$c->books_count.')'])->all();
    $authorOptions = $authors->mapWithKeys(fn ($a) => [$a->slug => $a->name])->all();
    $activeCategory = $filters['category'] ? $categories->firstWhere('slug', $filters['category']) : null;
    $activeAuthor = $filters['author'] ? $authors->firstWhere('slug', $filters['author']) : null;
    $sortActive = ($filters['sort'] ?? 'newest') !== 'newest';
    $activeCount = ($activeCategory ? 1 : 0) + ($activeAuthor ? 1 : 0) + ($sortActive ? 1 : 0);
    $hasFilters = $q || $activeCategory || $activeAuthor;
    $params = array_filter($filters, fn ($v, $k) => $v !== null && $v !== '' && ! ($k === 'sort' && $v === 'newest'), ARRAY_FILTER_USE_BOTH);
    $without = fn (string $key) => route('books.index', \Illuminate\Support\Arr::except($params, [$key]));
    $total = $books->total();
    $heading = $q ? 'Results for “'.$q.'”' : ($filters['sort'] === 'newest' && request()->has('sort') ? 'New releases' : 'All books');
@endphp
<x-layouts.app :title="$heading" :description="'Browse '.$total.' ebooks from independent authors: novels, essays and poetry as EPUB and PDF, ready to download.'" :noindex="(bool) $q">
    <div class="container-page py-section-sm">
        <header class="mb-8 grid gap-2">
            <h1 class="h1">{{ $heading }}</h1>
            <p class="hidden text-muted lg:block">{{ number_format($total) }} {{ \Illuminate\Support\Str::plural('book', $total) }}</p>
        </header>

        <form class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end" id="book-filters" data-strip-empty method="GET" action="{{ route('books.index') }}" role="search" aria-label="Search and filter books">
            <div class="field lg:max-w-sm lg:flex-1">
                <label class="label lg:sr-only" for="q">Search</label>
                <div class="relative">
                    <x-icon name="search" class="icon-sm pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted"/>
                    <input class="input pl-9 pr-12 lg:pr-3.5" id="q" type="search" name="q" value="{{ $q }}" placeholder="Search books and authors" maxlength="100" autocomplete="off">
                    <button class="btn btn-ghost btn-icon absolute right-0 top-0 lg:hidden" type="submit" aria-label="Search"><x-icon name="arrow-right"/></button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 lg:hidden">
                <button class="btn btn-secondary btn-sm js-only" type="button" aria-controls="filter-sheet" aria-expanded="false" data-filter-open>
                    <x-icon name="sliders-horizontal"/><span>Filters</span>@if ($activeCount)<span aria-hidden="true">· {{ $activeCount }}</span><span class="sr-only">, {{ $activeCount }} active</span>@endif
                </button>
                <p class="text-sm text-muted">{{ number_format($total) }} {{ \Illuminate\Support\Str::plural('book', $total) }}</p>
            </div>

            <dialog class="sheet-bottom filter-sheet" id="filter-sheet" aria-labelledby="filter-sheet-title">
                <div class="flex items-center justify-between px-5 pb-2 pt-4 lg:hidden">
                    <h2 class="h4" id="filter-sheet-title">Filters</h2>
                    <button class="btn btn-ghost btn-icon js-only" type="button" aria-label="Close filters" data-dialog-close><x-icon name="x"/></button>
                </div>
                <div class="grid grid-cols-1 gap-4 px-5 pb-5 lg:contents">
                    <x-select class="lg:w-52" name="category" label="Category" label-class="lg:sr-only" :options="$categoryOptions" :selected="$filters['category']" placeholder="All categories" data-autosubmit/>
                    <x-select class="lg:w-52" name="author" label="Author" label-class="lg:sr-only" :options="$authorOptions" :selected="$filters['author']" placeholder="All authors" data-autosubmit/>
                    <x-select class="lg:w-52" name="sort" label="Sort by" label-class="lg:sr-only" :options="$sorts" :selected="$filters['sort']" data-autosubmit data-default="newest"/>
                    <button class="btn btn-secondary hidden lg:inline-flex" type="submit">Apply</button>
                    <div class="flex flex-wrap gap-3 pt-2 lg:hidden">
                        <button class="btn btn-primary flex-1" type="submit">Show results</button>
                        <a class="btn btn-ghost" href="{{ route('books.index') }}">Clear filters</a>
                    </div>
                </div>
            </dialog>
        </form>

        @if ($hasFilters)
            <div class="mb-8 flex flex-wrap items-center gap-2" aria-label="Active filters" role="group">
                @if ($q)
                    <a class="badge badge-outline h-8 normal-case tracking-normal text-sm hover:border-ink hover:text-ink" href="{{ $without('q') }}" aria-label="Remove search “{{ $q }}”">“{{ $q }}”<x-icon name="x"/></a>
                @endif
                @if ($activeCategory)
                    <a class="badge badge-outline h-8 normal-case tracking-normal text-sm hover:border-ink hover:text-ink" href="{{ $without('category') }}" aria-label="Remove category {{ $activeCategory->name }}">{{ $activeCategory->name }}<x-icon name="x"/></a>
                @endif
                @if ($activeAuthor)
                    <a class="badge badge-outline h-8 normal-case tracking-normal text-sm hover:border-ink hover:text-ink" href="{{ $without('author') }}" aria-label="Remove author {{ $activeAuthor->name }}">{{ $activeAuthor->name }}<x-icon name="x"/></a>
                @endif
                <a class="link px-2 text-sm font-semibold" href="{{ route('books.index') }}">Clear all</a>
            </div>
        @endif

        @if ($books->isEmpty())
            @if ($hasFilters)
                <x-empty-state :heading="$q ? 'No books match “'.$q.'”' : 'No books match these filters'" icon="search">
                    Try a shorter search, or clear the filters.
                    <x-slot:actions>
                        <x-button variant="primary" :href="route('books.index')">Clear filters</x-button>
                    </x-slot:actions>
                </x-empty-state>
            @else
                <x-empty-state heading="The shelves are being stocked">New books arrive soon.</x-empty-state>
            @endif
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
