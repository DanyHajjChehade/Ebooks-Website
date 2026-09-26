@extends('layouts.app')
@section('description', $settings->tagline)
@section('content')
    <h1>{{ $settings->site_name }}</h1>
    <h2>Featured</h2>
    @include('partials.book-list', ['list' => $featured])
    <h2>New releases</h2>
    @include('partials.book-list', ['list' => $newReleases])
    <h2>Categories</h2>
    <ul>@foreach ($categories as $category)<li><a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a> ({{ $category->books_count }})</li>@endforeach</ul>
    <h2>Authors</h2>
    <ul>@foreach ($authors as $author)<li><a href="{{ route('authors.show', $author) }}">{{ $author->name }}</a> ({{ $author->books_count }})</li>@endforeach</ul>
    <h2>What readers say</h2>
    <ul>@foreach ($reviews as $review)<li>{{ $review->rating }}★ “{{ $review->body }}” — {{ $review->user->name }} on <a href="{{ route('books.show', $review->book) }}">{{ $review->book->title }}</a></li>@endforeach</ul>
@endsection
