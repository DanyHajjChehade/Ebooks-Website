@extends('errors.layout')
@section('title', 'Page not found')
@section('code', '404')
@section('heading', 'This page is out of print.')
@section('body', 'The link may be old, or the book has left our shelves. Try a search, or start from the front table.')
@section('actions')
    <form class="flex w-full flex-wrap gap-2" action="{{ url('/books') }}" method="GET" role="search">
        <label class="min-w-0 flex-1 basis-56">
            <span class="sr-only">Search books and authors</span>
            <input class="input" type="search" name="q" placeholder="Search books and authors">
        </label>
        <button class="btn btn-secondary" type="submit">Search</button>
    </form>
    <a class="btn btn-primary" href="{{ url('/books') }}">Browse all books</a>
    <a class="btn btn-ghost" href="{{ url('/') }}">Go to the home page</a>
@endsection
