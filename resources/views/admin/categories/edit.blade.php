@php $n = (int) ($category->books_count ?? 0); @endphp
<x-layouts.admin :title="'Edit '.$category->name">
    <x-admin.page-header :title="'Edit “'.$category->name.'”'" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Categories', route('admin.categories.index')], [$category->name]]">
        <x-slot:actions><x-button variant="ghost" :href="route('categories.show', $category)" trailing="arrow-up-right">View in shop</x-button></x-slot:actions>
    </x-admin.page-header>
    <x-admin.delete-blocked-alert noun="category"/>
    @include('admin.categories.form')
    <x-admin.danger-zone class="max-w-form" title="Delete this category" body="The shelf leaves the shop straight away."
        :blocked="$n > 0 ? 'You can’t delete this category while '.$n.' '.\Illuminate\Support\Str::plural('book', $n).' '.($n === 1 ? 'uses' : 'use').' it. Move those books first.' : null"
        :action="route('admin.categories.destroy', $category)" label="Delete category…"
        :confirm="'Delete “'.$category->name.'”?'" confirm-body="The shelf leaves the shop straight away." confirm-ok="Delete category" confirm-cancel="Keep category"/>
</x-layouts.admin>
