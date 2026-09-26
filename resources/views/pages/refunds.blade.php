@extends('layouts.app')
@section('title', 'Refund policy')
@section('content')
    <h1>Refund policy</h1>
    <p><strong>PLACEHOLDER LEGAL COPY — the store owner must replace this text with their own, reviewed Refund policy before launch.</strong></p>
    <p>{{ $settings->site_name }} sells digital ebooks. Questions: {{ $settings->contact_email ?? 'see the contact page' }}.</p>
@endsection
