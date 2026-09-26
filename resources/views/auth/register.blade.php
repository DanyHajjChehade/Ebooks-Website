<x-layouts.auth title="Create your account" description="Create a Book Planet account to buy ebooks and keep them in your library.">
    <div class="grid gap-2">
        <h1 class="h2">Create your account</h1>
        <p class="text-muted">Buy once and your books stay in your library, ready to download.</p>
    </div>
    <x-error-summary action="create your account"/>
    <form class="grid gap-5" method="POST" action="{{ route('register') }}">
        @csrf
        <x-input name="name" label="Name" autocomplete="name" required autofocus maxlength="255"/>
        <x-input name="email" label="Email" type="email" autocomplete="email" placeholder="you@example.com" required/>
        <x-input name="password" label="Password" type="password" autocomplete="new-password" hint="At least 8 characters." required minlength="8"/>
        <x-input name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required/>
        <button class="btn btn-primary btn-lg btn-block" type="submit" data-busy-label="Creating account…">Create account</button>
        <p class="text-xs text-muted">By creating an account you agree to our <a class="link" href="{{ route('pages.terms') }}">Terms</a> and <a class="link" href="{{ route('pages.privacy') }}">Privacy policy</a>.</p>
    </form>
    <p class="text-center text-sm">Already have an account? <a class="link" href="{{ route('login') }}">Log in</a></p>
</x-layouts.auth>
