{{-- Admin categories (DESIGN.md §5.4). Delete is proactively blocked while the category has books (A2). --}}
@php $q = $filters['q'] ?? null; @endphp
<x-layouts.admin title="Categories">
    <x-admin.page-header title="Categories" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Categories']]">
        <x-slot:actions><x-button variant="primary" icon="plus" :href="route('admin.categories.create')">Add category</x-button></x-slot:actions>
    </x-admin.page-header>
    <x-admin.delete-blocked-alert noun="category"/>
    <x-admin.filters :action="route('admin.categories.index')" label="Search categories" placeholder="Name" :q="$q" :active="(bool) $q"/>

    @if ($categories->isEmpty())
        @if ($q)
            <x-empty-state heading="No categories match" icon="search">Try a different name.</x-empty-state>
        @else
            <x-empty-state heading="No categories yet" icon="tags">
                <x-slot:actions><x-button variant="primary" icon="plus" :href="route('admin.categories.create')">Add your first category</x-button></x-slot:actions>
            </x-empty-state>
        @endif
    @else
        <div class="table-wrap">
            <table class="table">
                <caption class="sr-only">Categories</caption>
                <thead><tr><th scope="col">Name</th><th scope="col">Slug</th><th scope="col" class="num">Books</th><th scope="col" class="actions"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <th scope="row"><a class="link-quiet font-semibold" href="{{ route('admin.categories.edit', $category) }}">{{ $category->name }}</a></th>
                            <td class="font-mono text-xs text-muted">{{ $category->slug }}</td>
                            <td class="num">{{ number_format($category->books_count) }}</td>
                            <td class="actions">
                                <span class="inline-flex gap-1">
                                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.categories.edit', $category) }}" aria-label="Edit {{ $category->name }}"><x-icon name="pencil"/>Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        @if ($category->books_count > 0)
                                            <button class="btn btn-danger-quiet btn-sm btn-icon" type="submit" aria-disabled="true" data-confirm="x"><x-icon name="trash-2"/><span class="sr-only">Can’t delete {{ $category->name }}: {{ $category->books_count }} {{ \Illuminate\Support\Str::plural('book', $category->books_count) }}</span></button>
                                        @else
                                            <button class="btn btn-danger-quiet btn-sm btn-icon" type="submit" aria-label="Delete {{ $category->name }}"
                                                data-confirm="Delete “{{ $category->name }}”?" data-confirm-body="The shelf leaves the shop straight away."
                                                data-confirm-ok="Delete category" data-confirm-cancel="Keep category" data-confirm-busy="Deleting…"><x-icon name="trash-2"/></button>
                                        @endif
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$categories"/>
    @endif
</x-layouts.admin>
