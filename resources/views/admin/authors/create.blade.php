@extends('layouts.app')
@section('title', 'New author · Admin')
@section('content')
    <h1>New author</h1>
    <form method="POST" action="{{ route('admin.authors.store') }}" enctype="multipart/form-data">@csrf
        @include('admin.authors.form')
        <button type="submit">Create author</button>
    </form>
@endsection
