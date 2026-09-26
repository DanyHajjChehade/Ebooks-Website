@extends('layouts.app')
@section('title', 'Log in')
@section('content')
    <h1>Log in</h1>
    <form method="POST" action="{{ route('login') }}">@csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
        @error('email')<p>{{ $message }}</p>@enderror
        <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
        @error('password')<p>{{ $message }}</p>@enderror
        <label><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button type="submit">Log in</button>
    </form>
    <p><a href="{{ route('password.request') }}">Forgot your password?</a> · <a href="{{ route('register') }}">Create an account</a></p>
@endsection
