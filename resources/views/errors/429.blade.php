@extends('errors.layout')
@section('title', 'Too many requests')
@section('code', '429')
@section('heading', 'Too many tries at once.')
@section('body', 'Wait a minute, then try again.')
@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">Home</a>
@endsection
