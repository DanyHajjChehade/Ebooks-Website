@extends('layouts.app')
@section('title', 'Forgot password')
@section('content')
    <h1>Forgot your password?</h1>
    <form method="POST" action="{{ route('password.email') }}">@csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        @error('email')<p>{{ $message }}</p>@enderror
        <button type="submit">Email reset link</button>
    </form>
@endsection
