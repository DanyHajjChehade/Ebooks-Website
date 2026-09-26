@extends('errors.layout')
@section('title', 'Not allowed')
@section('code', '403')
@section('heading', 'This page isn’t on your shelf.')
@section('body', 'You don’t have permission to see it. If you think you should, log in with a different account.')
@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">Go to the home page</a>
    <a class="btn btn-ghost" href="{{ url('/login') }}">Log in</a>
@endsection
