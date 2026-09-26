{{-- Admin authors (DESIGN.md §5.4). Delete is proactively blocked while the author has books (A2). --}}
@php $q = $filters['q'] ?? null; @endphp
<x-layouts.admin title="Authors">
    <x-admin.page-header title="Authors" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Authors']]">
        <x-slot:actions><x-button variant="primary" icon="plus" :href="route('admin.authors.create')">Add author</x-button></x-slot:actions>
    </x-admin.page-header>
    <x-admin.delete-blocked-alert noun="author"/>
    <x-admin.filters :action="route('admin.authors.index')" label="Search authors" placeholder="Name" :q="$q" :active="(bool) $q"/>

    @if ($authors->isEmpty())
        @if ($q)
            <x-empty-state heading="No authors match" icon="search">Try a different name.</x-empty-state>
        @else
            <x-empty-state heading="No authors yet" icon="feather">
                <x-slot:actions><x-button variant="primary" icon="plus" :href="route('admin.authors.create')">Add your first author</x-button></x-slot:actions>
            </x-empty-state>
        @endif
    @else
        <div class="table-wrap">
            <table class="table">
                <caption class="sr-only">Authors</caption>
                <thead><tr><th scope="col">Author</th><th scope="col" class="num">Books</th><th scope="col">Updated</th><th scope="col" class="actions"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($authors as $author)
                        <tr>
                            <th scope="row">
                                <span class="flex items-center gap-3">
                                    <x-avatar :name="$author->name" :id="$author->id" size="sm" :photo="$author->photo_url"/>
                                    <a class="link-quiet font-semibold" href="{{ route('admin.authors.edit', $author) }}">{{ $author->name }}</a>
                                </span>
                            </th>
                            <td class="num">{{ number_format($author->books_count) }}</td>
                            <td class="whitespace-nowrap"><time datetime="{{ $author->updated_at->toIso8601String() }}">{{ $author->updated_at->format('M j, Y') }}</time></td>
                            <td class="actions">
                                <span class="inline-flex gap-1">
                                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.authors.edit', $author) }}" aria-label="Edit {{ $author->name }}"><x-icon name="pencil"/>Edit</a>
                                    <form method="POST" action="{{ route('admin.authors.destroy', $author) }}">
                                        @csrf
                                        @method('DELETE')
                                        @if ($author->books_count > 0)
                                            <button class="btn btn-danger-quiet btn-sm btn-icon" type="submit" aria-disabled="true" data-confirm="x"><x-icon name="trash-2"/><span class="sr-only">Can’t delete {{ $author->name }}: {{ $author->books_count }} {{ \Illuminate\Support\Str::plural('book', $author->books_count) }}</span></button>
                                        @else
                                            <button class="btn btn-danger-quiet btn-sm btn-icon" type="submit" aria-label="Delete {{ $author->name }}"
                                                data-confirm="Delete {{ $author->name }}?" data-confirm-body="They leave the author list straight away."
                                                data-confirm-ok="Delete author" data-confirm-cancel="Keep author" data-confirm-busy="Deleting…"><x-icon name="trash-2"/></button>
                                        @endif
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$authors"/>
    @endif
</x-layouts.admin>
