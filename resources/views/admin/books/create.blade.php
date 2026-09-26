<x-layouts.admin title="Add a book">
    <x-admin.page-header title="Add a book" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Books', route('admin.books.index')], ['Add a book']]"/>
    @include('admin.books.form')
</x-layouts.admin>
