<div class="gallery" id="gallery">
    <h2 class="main-title">Books</h2>
    <div class="container">

        @foreach ($books as $book)
        <x-Home.BookItemSection :book="$book"/>
        @endforeach

    </div>
</div>
