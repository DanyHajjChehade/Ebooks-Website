@extends('layouts.app')
@section('title', 'Books')
@section('content')
    <h1>Books</h1>
    <form method="GET" action="{{ route('books.index') }}" role="search">
        <label>Search <input type="search" name="q" value="{{ $filters['q'] }}"></label>
        <label>Category
            <select name="category"><option value="">All</option>
                @foreach ($categories as $category)<option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }} ({{ $category->books_count }})</option>@endforeach
            </select></label>
        <label>Author
            <select name="author"><option value="">All</option>
                @foreach ($authors as $author)<option value="{{ $author->slug }}" @selected($filters['author'] === $author->slug)>{{ $author->name }}</option>@endforeach
            </select></label>
        <label>Sort
            <select name="sort">
                @foreach (['newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'title' => 'Title'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
        <button type="submit">Apply</button>
    </form>
    <p>{{ $books->total() }} books</p>
    @include('partials.book-list', ['list' => $books])
    {{ $books->links() }}
@endsection
