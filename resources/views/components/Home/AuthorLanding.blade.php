<div class="landing">
    <div class="container">
        <div class="text">
            <h1>Hey , this is {{ $author->first_name }} {{ $author->last_name }}</h1>
            <p>{{ $author->description }}</p>
        </div>

        <div class="image">
            <img src="{{ asset($author->author_image) }}" alt="Author Image" />
        </div>
    </div>
    <a href="#books" class="go-down"><i class="fas fa-angle-double-down fa-2x"></i></a>
</div>
