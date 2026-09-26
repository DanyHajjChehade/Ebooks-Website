<x-empty-state heading="Your cart is empty" icon="shopping-bag">
    Find something worth staying up for.
    <x-slot:actions>
        <x-button variant="primary" :href="route('books.index')" trailing="arrow-right">Browse the books</x-button>
        @auth<x-button variant="ghost" :href="route('library.index')">Your library</x-button>@endauth
    </x-slot:actions>
</x-empty-state>
