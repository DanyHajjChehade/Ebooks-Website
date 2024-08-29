<div class="landing">
    <div class="container">
        <div class="text">
            <h1>{{ $category->name }}</h1>
            <p>{{ $category->description }}</p>
        </div>

        <div class="image">
            <img src="{{ asset($category->image) }}" alt="Author Image" />
        </div>
    </div>
    <a href="#books" class="go-down"><i class="fas fa-angle-double-down fa-2x"></i></a>
</div>
