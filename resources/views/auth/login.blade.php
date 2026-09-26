<x-layouts.auth title="Log in" description="Log in to your Book Planet library.">
    <div class="grid gap-2">
        <h1 class="h2">Log in</h1>
        <p class="text-muted">Welcome back. Your library is waiting.</p>
    </div>
    @if (session('status'))
        <x-alert variant="success">{{ session('status') }}</x-alert>
    @endif
    <form class="grid gap-5" method="POST" action="{{ route('login') }}">
        @csrf
        <x-input name="email" label="Email" type="email" autocomplete="email" placeholder="you@example.com" required autofocus/>
        <x-input name="password" label="Password" type="password" autocomplete="current-password" required/>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <x-checkbox name="remember" label="Remember me"/>
            <a class="link text-sm" href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <button class="btn btn-primary btn-lg btn-block" type="submit" data-busy-label="Logging in…">Log in</button>
    </form>
    <p class="text-center text-sm">New to {{ $settings->site_name ?: 'Book Planet' }}? <a class="link" href="{{ route('register') }}">Create an account</a></p>
</x-layouts.auth>
