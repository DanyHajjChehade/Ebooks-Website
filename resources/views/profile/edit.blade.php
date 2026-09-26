@extends('layouts.app')
@section('title', 'Your profile')
@section('content')
    <h1>Profile</h1>

    <h2>Account details</h2>
    <form method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')
        <label>Name <input name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"></label>
        @error('name')<p>{{ $message }}</p>@enderror
        <label>Email <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"></label>
        @error('email')<p>{{ $message }}</p>@enderror
        <button type="submit">Save</button>
    </form>

    <h2>Change password</h2>
    <form method="POST" action="{{ route('password.update') }}">@csrf @method('PUT')
        <label>Current password <input type="password" name="current_password" autocomplete="current-password"></label>
        @error('current_password', 'updatePassword')<p>{{ $message }}</p>@enderror
        <label>New password <input type="password" name="password" autocomplete="new-password"></label>
        @error('password', 'updatePassword')<p>{{ $message }}</p>@enderror
        <label>Confirm <input type="password" name="password_confirmation" autocomplete="new-password"></label>
        <button type="submit">Update password</button>
    </form>

    <h2>Delete account</h2>
    <p>Your library and reviews will be deleted. Order records are kept for accounting.</p>
    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete your account permanently?')">@csrf @method('DELETE')
        <label>Password <input type="password" name="password" autocomplete="current-password"></label>
        @error('password', 'userDeletion')<p>{{ $message }}</p>@enderror
        <button type="submit">Delete account</button>
    </form>
@endsection
