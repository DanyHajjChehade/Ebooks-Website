@extends('layouts.app')
@section('title', 'Terms of service')
@section('content')
    <h1>Terms of service</h1>
    <p><strong>PLACEHOLDER LEGAL COPY — the store owner must replace this text with their own, reviewed Terms of service before launch.</strong></p>
    <p>{{ $settings->site_name }} sells digital ebooks. Questions: {{ $settings->contact_email ?? 'see the contact page' }}.</p>
@endsection
