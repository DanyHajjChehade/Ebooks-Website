@extends('errors.layout')
@php
    // The Referer header, not the session: error pages never read the session.
    $back = request()->headers->get('referer');
    $back = $back && parse_url($back, PHP_URL_HOST) === request()->getHost() ? $back : url('/');
@endphp
@section('title', 'Page expired')
@section('code', '419')
@section('heading', 'This page timed out.')
@section('body', 'For your security, forms expire after a while. Go back, refresh the page and try again.')
@section('actions')
    <a class="btn btn-primary" href="{{ $back }}">Go back</a>
    <a class="btn btn-ghost" href="{{ url('/') }}">Home</a>
@endsection
