@extends('layouts.app')
@section('title', 'Edit '.$book->title.' · Admin')
@section('content')
    <h1>Edit “{{ $book->title }}”</h1>
    <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data">@csrf @method('PUT')
        @include('admin.books.form')
        <button type="submit">Save changes</button>
    </form>
    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Remove this book from the store?')">@csrf @method('DELETE')<button type="submit">Delete</button></form>
@endsection
