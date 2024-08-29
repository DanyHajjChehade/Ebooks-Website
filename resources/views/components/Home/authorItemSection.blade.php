<div class="box" id="authors">
    <img src="{{ $author->author_image }}" alt="" />
    <div class="content">
        <h3>{{ $author->first_name }} {{ $author->last_name }}</h3>
    </div>
    <div class="info">
        <a href="/author/{{ $author->first_name }}/books">Author Profile</a>
        <i class="fas fa-long-arrow-alt-right"></i>
    </div>
</div>

