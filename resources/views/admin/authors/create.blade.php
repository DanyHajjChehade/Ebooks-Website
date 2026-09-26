<x-layouts.admin title="Add an author">
    <x-admin.page-header title="Add an author" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Authors', route('admin.authors.index')], ['Add an author']]"/>
    @include('admin.authors.form')
</x-layouts.admin>
