@extends('layouts.app')
@section('title', 'New book · Admin')
@section('content')
    <h1>New book</h1>
    <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data">@csrf
        @include('admin.books.form')
        <button type="submit">Create book</button>
    </form>
@endsection
