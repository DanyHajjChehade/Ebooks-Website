@extends('layouts.app')
@section('title', 'Authors')
@section('content')
    <h1>Authors</h1>
    <form method="GET" action="{{ route('authors.index') }}" role="search">
        <label>Search authors <input type="search" name="q" value="{{ $filters['q'] }}"></label>
        <button type="submit">Search</button>
    </form>
    <ul>@forelse ($authors as $author)<li><a href="{{ route('authors.show', $author) }}">{{ $author->name }}</a> ({{ $author->books_count }} books)</li>@empty<li>No authors found.</li>@endforelse</ul>
    {{ $authors->links() }}
@endsection
