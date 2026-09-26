<x-layouts.auth title="Reset your password">
    <div class="grid gap-2">
        <h1 class="h2">Reset your password</h1>
        <p class="text-muted">Enter the email you signed up with and we’ll send you a link to choose a new password.</p>
    </div>
    @if (session('status'))
        <x-alert variant="success">{{ session('status') }}</x-alert>
    @endif
    <form class="grid gap-5" method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-input name="email" label="Email" type="email" autocomplete="email" placeholder="you@example.com" required autofocus/>
        <button class="btn btn-primary btn-lg btn-block" type="submit" data-busy-label="Sending…">Email me a reset link</button>
    </form>
    <p class="text-center text-sm"><a class="link" href="{{ route('login') }}">Back to log in</a></p>
</x-layouts.auth>
