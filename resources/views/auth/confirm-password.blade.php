@extends('layouts.app')
@section('title', 'Confirm password')
@section('content')
    <h1>Confirm your password</h1>
    <form method="POST" action="{{ route('password.confirm') }}">@csrf
        <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
        @error('password')<p>{{ $message }}</p>@enderror
        <button type="submit">Confirm</button>
    </form>
@endsection
