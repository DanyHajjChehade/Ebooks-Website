@php $n = (int) ($author->books_count ?? 0); @endphp
<x-layouts.admin :title="'Edit '.$author->name">
    <x-admin.page-header :title="'Edit '.$author->name" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Authors', route('admin.authors.index')], [$author->name]]">
        <x-slot:actions><x-button variant="ghost" :href="route('authors.show', $author)" trailing="arrow-up-right">View in shop</x-button></x-slot:actions>
    </x-admin.page-header>
    <x-admin.delete-blocked-alert noun="author"/>
    @include('admin.authors.form')
    <x-admin.danger-zone class="max-w-form" title="Delete this author" body="They leave the author list straight away."
        :blocked="$n > 0 ? 'You can’t delete this author while '.$n.' '.\Illuminate\Support\Str::plural('book', $n).' '.($n === 1 ? 'is' : 'are').' by them. Reassign or delete those books first.' : null"
        :action="route('admin.authors.destroy', $author)" label="Delete author…"
        :confirm="'Delete '.$author->name.'?'" confirm-body="They leave the author list straight away." confirm-ok="Delete author" confirm-cancel="Keep author"/>
</x-layouts.admin>
