<x-layouts.admin :title="'Edit “'.$book->title.'”'">
    <x-admin.page-header :title="'Edit “'.$book->title.'”'" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Books', route('admin.books.index')], [$book->title]]">
        @if ($book->isPubliclyVisible())
            <x-slot:actions>
                <x-button variant="ghost" :href="route('books.show', $book)" trailing="arrow-up-right">View in shop</x-button>
            </x-slot:actions>
        @endif
    </x-admin.page-header>
    @include('admin.books.form')
    <x-admin.danger-zone title="Delete this book" body="It leaves the shop straight away. Readers who bought it keep it in their library."
        :action="route('admin.books.destroy', $book)" label="Delete book…"
        :confirm="'Delete “'.$book->title.'”?'" confirm-body="It leaves the shop straight away. Readers who bought it keep it in their library."
        confirm-ok="Delete book" confirm-cancel="Keep book"/>
</x-layouts.admin>
