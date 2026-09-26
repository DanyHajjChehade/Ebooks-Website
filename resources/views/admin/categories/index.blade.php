@extends('layouts.app')
@section('title', 'Categories · Admin')
@section('content')
    <h1>Categories</h1>
    <p><a href="{{ route('admin.categories.create') }}">New category</a></p>
    <form method="GET" action="{{ route('admin.categories.index') }}" role="search"><label>Search <input type="search" name="q" value="{{ $filters['q'] }}"></label><button type="submit">Search</button></form>
    <ul>
        @forelse ($categories as $category)
            <li>{{ $category->name }} ({{ $category->books_count }} books)
                <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></li>
        @empty
            <li>No categories.</li>
        @endforelse
    </ul>
    {{ $categories->links() }}
@endsection
