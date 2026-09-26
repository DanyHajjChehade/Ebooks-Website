@extends('layouts.app')
@section('title', 'Edit '.$author->name.' · Admin')
@section('content')
    <h1>Edit {{ $author->name }}</h1>
    <form method="POST" action="{{ route('admin.authors.update', $author) }}" enctype="multipart/form-data">@csrf @method('PUT')
        @include('admin.authors.form')
        <button type="submit">Save changes</button>
    </form>
@endsection
