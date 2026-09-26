{{--
    PLACEHOLDER layout (backend). Unstyled on purpose; the frontend replaces it.
    Shared data: $settings, $cartCount, $cartBookIds, $ownedBookIds.
    Sections: title, description, content.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ $settings->site_name }}</title>
    <meta name="description" content="@yield('description', $settings->tagline)">
    <link rel="canonical" href="{{ url()->current() }}">
</head>
<body>
<header>
    <nav aria-label="Main">
        <a href="{{ route('home') }}">{{ $settings->site_name }}</a> |
        <a href="{{ route('books.index') }}">Books</a> |
        <a href="{{ route('authors.index') }}">Authors</a> |
        <a href="{{ route('cart.index') }}">Cart ({{ $cartCount }})</a> |
        @auth
            <a href="{{ route('library.index') }}">Library</a> |
            <a href="{{ route('orders.index') }}">Orders</a> |
            <a href="{{ route('profile.edit') }}">Profile</a> |
            @can('access-admin') <a href="{{ route('admin.dashboard') }}">Admin</a> | @endcan
            <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf <button type="submit">Log out</button></form>
        @else
            <a href="{{ route('login') }}">Log in</a> |
            <a href="{{ route('register') }}">Register</a>
        @endauth
    </nav>
    @if (request()->routeIs('admin.*'))
        <nav aria-label="Admin">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> |
            <a href="{{ route('admin.books.index') }}">Books</a> |
            <a href="{{ route('admin.authors.index') }}">Authors</a> |
            <a href="{{ route('admin.categories.index') }}">Categories</a> |
            <a href="{{ route('admin.orders.index') }}">Orders</a> |
            <a href="{{ route('admin.users.index') }}">Users</a> |
            <a href="{{ route('admin.reviews.index') }}">Reviews</a> |
            <a href="{{ route('admin.settings.edit') }}">Settings</a>
        </nav>
    @endif
</header>

@if (session('status'))<p role="status">{{ session('status') }}</p>@endif
@if (session('error'))<p role="alert">{{ session('error') }}</p>@endif
@error('delete')<p role="alert">{{ $message }}</p>@enderror

<main>
    @yield('content')
</main>

<footer>
    <p>{{ $settings->tagline }}</p>
    <a href="{{ route('pages.contact') }}">Contact</a> |
    <a href="{{ route('pages.terms') }}">Terms</a> |
    <a href="{{ route('pages.privacy') }}">Privacy</a> |
    <a href="{{ route('pages.refunds') }}">Refund policy</a>
    @foreach ($settings->socialLinks() as $network => $url)
        | <a href="{{ $url }}" rel="noopener">{{ $network }}</a>
    @endforeach
</footer>
</body>
</html>
