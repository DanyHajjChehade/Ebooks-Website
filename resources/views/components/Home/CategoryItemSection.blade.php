<div id="categories" class="box passion">
    <div class="img-holder"><img src="{{ $category->image }}" alt="Category Image"/></div>
    <h2>{{ $category->name }}</h2>
    <p>{{ $category->description }}</p>
    <a href="/category/{{ $category->name }}/books">Choose</a>
</div>
