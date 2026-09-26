@extends('layouts.app')
@section('title', 'Verify your email')
@section('content')
    <h1>Verify your email</h1>
    <p>Follow the link we emailed you. Didn't get it?</p>
    <form method="POST" action="{{ route('verification.send') }}">@csrf <button type="submit">Resend verification email</button></form>
@endsection
