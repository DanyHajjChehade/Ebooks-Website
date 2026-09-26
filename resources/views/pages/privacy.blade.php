@extends('layouts.app')
@section('title', 'Privacy policy')
@section('content')
    <h1>Privacy policy</h1>
    <p><strong>PLACEHOLDER LEGAL COPY — the store owner must replace this text with their own, reviewed Privacy policy before launch.</strong></p>
    <p>{{ $settings->site_name }} sells digital ebooks. Questions: {{ $settings->contact_email ?? 'see the contact page' }}.</p>
@endsection
