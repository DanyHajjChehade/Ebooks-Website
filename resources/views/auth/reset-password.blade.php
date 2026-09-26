@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
    <h1>Reset password</h1>
    <form method="POST" action="{{ route('password.store') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label>Email <input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username"></label>
        @error('email')<p>{{ $message }}</p>@enderror
        <label>New password <input type="password" name="password" required autocomplete="new-password"></label>
        @error('password')<p>{{ $message }}</p>@enderror
        <label>Confirm password <input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        <button type="submit">Reset password</button>
    </form>
@endsection
