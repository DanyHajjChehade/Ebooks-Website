@extends('layouts.app')
@section('title', $category->name)
@section('description', \Illuminate\Support\Str::limit((string) $category->description, 155))
@section('content')
    <h1>{{ $category->name }}</h1>
    <p>{{ $category->description }}</p>
    @include('partials.book-list', ['list' => $books])
    {{ $books->links() }}
@endsection
