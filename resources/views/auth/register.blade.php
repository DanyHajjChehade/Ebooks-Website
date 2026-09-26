@extends('layouts.app')
@section('title', 'Create an account')
@section('content')
    <h1>Create an account</h1>
    <form method="POST" action="{{ route('register') }}">@csrf
        <label>Name <input name="name" value="{{ old('name') }}" required autofocus autocomplete="name"></label>
        @error('name')<p>{{ $message }}</p>@enderror
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></label>
        @error('email')<p>{{ $message }}</p>@enderror
        <label>Password <input type="password" name="password" required autocomplete="new-password"></label>
        @error('password')<p>{{ $message }}</p>@enderror
        <label>Confirm password <input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        <button type="submit">Register</button>
    </form>
    <p><a href="{{ route('login') }}">Already registered?</a></p>
@endsection
