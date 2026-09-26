@extends('layouts.app')
@section('title', 'Your library')
@section('content')
    <h1>Your library</h1>
    <ul>
        @forelse ($books as $book)
            <li>{{ $book->title }} by {{ $book->author->name }} ({{ strtoupper($book->file_format) }})
                — <a href="{{ route('library.download', $book) }}">Download</a>
                @unless ($book->trashed()) · <a href="{{ route('books.show', $book) }}">Book page</a>@endunless
            </li>
        @empty
            <li>No books yet. <a href="{{ route('books.index') }}">Find your next read</a></li>
        @endforelse
    </ul>
@endsection
