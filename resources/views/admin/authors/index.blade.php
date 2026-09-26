@extends('layouts.app')
@section('title', 'Authors · Admin')
@section('content')
    <h1>Authors</h1>
    <p><a href="{{ route('admin.authors.create') }}">New author</a></p>
    <form method="GET" action="{{ route('admin.authors.index') }}" role="search"><label>Search <input type="search" name="q" value="{{ $filters['q'] }}"></label><button type="submit">Search</button></form>
    <ul>
        @forelse ($authors as $author)
            <li>{{ $author->name }} ({{ $author->books_count }} books)
                <a href="{{ route('admin.authors.edit', $author) }}">Edit</a>
                <form method="POST" action="{{ route('admin.authors.destroy', $author) }}" style="display:inline" onsubmit="return confirm('Delete this author?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></li>
        @empty
            <li>No authors.</li>
        @endforelse
    </ul>
    {{ $authors->links() }}
@endsection
