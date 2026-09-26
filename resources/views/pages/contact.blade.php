@extends('layouts.app')
@section('title', 'Contact')
@section('content')
    <h1>Contact</h1>
    <ul>
        @if ($settings->contact_email)<li>Email: <a href="mailto:{{ $settings->contact_email }}">{{ $settings->contact_email }}</a></li>@endif
        @if ($settings->phone)<li>Phone: {{ $settings->phone }}</li>@endif
        @if ($settings->address)<li>Address: {{ $settings->address }}</li>@endif
    </ul>
@endsection
