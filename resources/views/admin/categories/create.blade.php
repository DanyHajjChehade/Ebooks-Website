<x-layouts.admin title="Add a category">
    <x-admin.page-header title="Add a category" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Categories', route('admin.categories.index')], ['Add a category']]"/>
    @include('admin.categories.form')
</x-layouts.admin>
