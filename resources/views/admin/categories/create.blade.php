@extends('layouts.app')
@section('title', 'New category · Admin')
@section('content')
    <h1>New category</h1>
    <form method="POST" action="{{ route('admin.categories.store') }}">@csrf
        @include('admin.categories.form')
        <button type="submit">Create category</button>
    </form>
@endsection
