@extends('errors.layout')
@section('title', 'Server error')
@section('code', '500')
@section('heading', 'Something went wrong on our side.')
@section('body', 'We’ve logged the error. Try again in a moment. If it keeps happening, contact us.')
@section('actions')
    <a class="btn btn-primary" href="{{ request()->fullUrl() }}">Try again</a>
    <a class="btn btn-ghost" href="{{ url('/') }}">Home</a>
@endsection
