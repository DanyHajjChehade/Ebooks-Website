@extends('layouts.app')
@section('title', $author->name)
@section('description', \Illuminate\Support\Str::limit((string) $author->bio, 155))
@section('content')
    <h1>{{ $author->name }}</h1>
    @if ($author->photo_url)<img src="{{ $author->photo_url }}" alt="{{ $author->name }}" width="120">@endif
    <p>{{ $author->bio }}</p>
    @include('partials.book-list', ['list' => $books])
    {{ $books->links() }}
@endsection
