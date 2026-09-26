<x-layouts.auth title="Choose a new password">
    <h1 class="h2">Choose a new password</h1>
    <form class="grid gap-5" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-input name="email" label="Email" type="email" autocomplete="email" :value="$email" required/>
        <x-input name="password" label="New password" type="password" autocomplete="new-password" hint="At least 8 characters." required autofocus minlength="8"/>
        <x-input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required/>
        <button class="btn btn-primary btn-lg btn-block" type="submit" data-busy-label="Saving…">Save new password</button>
    </form>
</x-layouts.auth>
