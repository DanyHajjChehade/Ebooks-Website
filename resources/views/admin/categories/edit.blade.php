@extends('layouts.app')
@section('title', 'Edit '.$category->name.' · Admin')
@section('content')
    <h1>Edit {{ $category->name }}</h1>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT')
        @include('admin.categories.form')
        <button type="submit">Save changes</button>
    </form>
@endsection
