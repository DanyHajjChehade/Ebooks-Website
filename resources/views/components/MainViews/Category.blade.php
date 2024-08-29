<x-Home.Layout>
    <x-Home.CategoryLanding :category="$category"/>
    <x-Home.BookCategorySection :cart="$cart" :category="$category" :books="$books"/>
</x-Home.Layout>
