<x-layouts.auth title="Confirm your password">
    <div class="grid gap-2">
        <h1 class="h2">Confirm your password</h1>
        <p class="text-muted">You’re about to change something sensitive. Enter your password to continue.</p>
    </div>
    <form class="grid gap-5" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <x-input name="password" label="Password" type="password" autocomplete="current-password" required autofocus/>
        <button class="btn btn-primary btn-lg btn-block" type="submit" data-busy-label="Confirming…">Confirm</button>
    </form>
</x-layouts.auth>
