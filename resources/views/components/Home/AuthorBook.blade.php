<div class="gallery" id="gallery">
    <h2 class="main-title">{{ $author->first_name }} Books</h2>
    <div class="container">
        @foreach ($author->books as $book)
        <x-Home.BookCategorySectionItem :book="$book"/>
        @endforeach
    </div>
</div>
