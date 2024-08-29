<x-Home.Layout>
    <x-Home.Landing/>
    <x-Home.authorSection :authors="$authors"/>
    <x-Home.CategorySection :categories="$categories"/>
    <x-Home.BookHomeSection :cart="$cart" :books="$books"/>
    <x-Home.testimonialsSection :comments="$comments"/>
    <x-Home.Comment/>
</x-Home.Layout>
