@extends('layouts.app')
@section('title', 'Users · Admin')
@section('content')
    <h1>Users</h1>
    <form method="GET" action="{{ route('admin.users.index') }}" role="search">
        <label>Search <input type="search" name="q" value="{{ $filters['q'] }}"></label>
        <label>Role <select name="role"><option value="">All</option>
            <option value="admin" @selected($filters['role'] === 'admin')>Admins</option>
            <option value="customer" @selected($filters['role'] === 'customer')>Customers</option></select></label>
        <button type="submit">Filter</button>
    </form>
    <ul>
        @forelse ($users as $user)
            <li>{{ $user->name }} &lt;{{ $user->email }}&gt; — {{ $user->is_admin ? 'Admin' : 'Customer' }} — {{ $user->orders_count }} paid orders — {{ $user->books_count }} books
                @unless (auth()->user()->is($user))
                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" style="display:inline" onsubmit="return confirm('Change this user\'s role?')">@csrf @method('PATCH')
                        <button type="submit">{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</button>
                    </form>
                @endunless
            </li>
        @empty
            <li>No users.</li>
        @endforelse
    </ul>
    {{ $users->links() }}
@endsection
