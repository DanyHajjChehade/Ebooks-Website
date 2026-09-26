<x-layouts.auth title="Check your inbox">
    <div class="grid gap-2">
        <h1 class="h2">Check your inbox</h1>
        <p class="text-muted">We sent a link to <strong class="text-ink">{{ auth()->user()->email }}</strong>. Select it to confirm your address. You can keep shopping while you wait.</p>
    </div>
    @if (session('status'))
        <x-alert variant="success">{{ session('status') === 'verification-link-sent' ? 'A new link is on its way.' : session('status') }}</x-alert>
    @endif
    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn btn-secondary" type="submit" data-busy-label="Sending…">Send the link again</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-ghost" type="submit">Log out</button>
        </form>
    </div>
    <p class="text-sm"><a class="link" href="{{ route('home') }}">Keep shopping</a></p>
</x-layouts.auth>
