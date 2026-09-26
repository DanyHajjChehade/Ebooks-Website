{{-- Admin books (DESIGN.md §5.4): status tabs, search, table, delete confirm. --}}
@php
    $status = $filters['status'] ?? null;
    $q = $filters['q'] ?? null;
    $tab = fn (?string $s) => route('admin.books.index', array_filter(['status' => $s, 'q' => $q]));
@endphp
<x-layouts.admin title="Books">
    <x-admin.page-header title="Books" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Books']]">
        <x-slot:actions>
            <x-button variant="primary" icon="plus" :href="route('admin.books.create')">Add book</x-button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.status-tabs :tabs="[
        ['All', $tab(null), $status === null],
        ['Published', $tab('published'), $status === 'published'],
        ['Drafts', $tab('draft'), $status === 'draft'],
        ['Scheduled', $tab('scheduled'), $status === 'scheduled'],
        ['Featured', $tab('featured'), $status === 'featured'],
    ]"/>

    <x-admin.filters :action="route('admin.books.index')" label="Search books" placeholder="Title, author or ISBN" :q="$q" :active="$q || $status">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    </x-admin.filters>

    @if ($books->isEmpty())
        @if ($q || $status)
            <x-empty-state heading="No books match" icon="search">Try a different search or status.</x-empty-state>
        @else
            <x-empty-state heading="No books yet" icon="book-open">
                Add a book with its cover and EPUB or PDF file.
                <x-slot:actions><x-button variant="primary" icon="plus" :href="route('admin.books.create')">Add your first book</x-button></x-slot:actions>
            </x-empty-state>
        @endif
    @else
        <div class="table-wrap">
            <table class="table">
                <caption class="sr-only">Books</caption>
                <thead>
                    <tr><th scope="col">Book</th><th scope="col">Category</th><th scope="col" class="num">Price</th><th scope="col">Status</th><th scope="col" class="num">Sold</th><th scope="col">Updated</th><th scope="col" class="actions"><span class="sr-only">Actions</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($books as $book)
                        @php $scheduled = $book->is_published && $book->published_at?->isFuture(); @endphp
                        <tr>
                            <th class="min-w-64" scope="row">
                                <span class="flex items-center gap-3">
                                    <x-book-cover class="w-10 flex-none" :book="$book"/>
                                    <span class="grid min-w-0">
                                        <a class="link-quiet max-w-64 truncate font-semibold" href="{{ route('admin.books.edit', $book) }}">{{ $book->title }}</a>
                                        <span class="truncate font-normal text-muted">{{ $book->author?->name }}</span>
                                    </span>
                                </span>
                            </th>
                            <td>{{ $book->category?->name }}</td>
                            <td class="num"><x-price class="justify-end" :book="$book"/></td>
                            <td>
                                <span class="flex flex-wrap gap-1">
                                    @if ($scheduled)
                                        <x-badge variant="warning">Scheduled</x-badge>
                                    @elseif ($book->is_published)
                                        <x-badge variant="success">Published</x-badge>
                                    @else
                                        <x-badge>Draft</x-badge>
                                    @endif
                                    @if ($book->is_featured)<x-badge variant="accent">Featured</x-badge>@endif
                                </span>
                            </td>
                            <td class="num">{{ number_format((int) $book->sales_count) }}</td>
                            <td class="whitespace-nowrap"><time datetime="{{ $book->updated_at->toIso8601String() }}">{{ $book->updated_at->format('M j, Y') }}</time></td>
                            <td class="actions">
                                <span class="inline-flex gap-1">
                                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.books.edit', $book) }}" aria-label="Edit {{ $book->title }}"><x-icon name="pencil"/>Edit</a>
                                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger-quiet btn-sm btn-icon" type="submit" aria-label="Delete {{ $book->title }}"
                                            data-confirm="Delete “{{ $book->title }}”?" data-confirm-body="It leaves the shop straight away. Readers who bought it keep it in their library."
                                            data-confirm-ok="Delete book" data-confirm-cancel="Keep book" data-confirm-busy="Deleting…"><x-icon name="trash-2"/></button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$books"/>
    @endif
</x-layouts.admin>
