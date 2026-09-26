{{-- Authors directory (DESIGN.md §5.1 authors.index), with ?q= search (A9). --}}
@php
    $q = $filters['q'] ?? null;
    $total = $authors->total();
@endphp
<x-layouts.app :title="$q ? 'Authors matching “'.$q.'”' : 'Authors'" description="Meet the writers behind our ebooks: novelists, essayists and poets." :noindex="(bool) $q">
    <div class="container-page py-section-sm">
        <header class="mb-8 grid gap-2">
            <h1 class="h1">Authors</h1>
            <p class="text-muted">{{ number_format($total) }} {{ \Illuminate\Support\Str::plural('author', $total) }}</p>
        </header>

        <form class="mb-10 flex max-w-xl flex-wrap gap-3" data-strip-empty method="GET" action="{{ route('authors.index') }}" role="search">
            <label class="relative min-w-0 flex-1 basis-56">
                <span class="sr-only">Search authors</span>
                <x-icon name="search" class="icon-sm pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted"/>
                <input class="input pl-9" type="search" name="q" value="{{ $q }}" placeholder="Search authors" maxlength="100" autocomplete="off">
            </label>
            <button class="btn btn-secondary" type="submit">Search</button>
            @if ($q)<a class="btn btn-ghost" href="{{ route('authors.index') }}">Clear</a>@endif
        </form>

        @if ($authors->isEmpty())
            @if ($q)
                <x-empty-state :heading="'No authors match “'.$q.'”'" icon="search">
                    Try a shorter search.
                    <x-slot:actions><x-button variant="primary" :href="route('authors.index')">Clear search</x-button></x-slot:actions>
                </x-empty-state>
            @else
                <x-empty-state heading="The authors are on their way">New books, and the people who wrote them, arrive soon.</x-empty-state>
            @endif
        @else
            <ul class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                @foreach ($authors as $author)
                    <li class="relative grid content-start justify-items-center gap-2 text-center">
                        <x-avatar class="max-sm:[--size:5rem]" :name="$author->name" :id="$author->id" size="xl" :photo="$author->photo_url"/>
                        <h2 class="font-serif text-lg leading-tight"><a class="text-ink no-underline after:absolute after:inset-0 hover:underline" href="{{ route('authors.show', $author) }}">{{ $author->name }}</a></h2>
                        <p class="text-sm text-muted">{{ $author->books_count }} {{ \Illuminate\Support\Str::plural('book', $author->books_count) }}</p>
                    </li>
                @endforeach
            </ul>
            <x-pagination class="mt-12" :paginator="$authors"/>
        @endif
    </div>
</x-layouts.app>
