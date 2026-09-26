{{-- Profile (DESIGN.md §5.3): details, password, delete account (A7: needs the current password). --}}
@php
    $words = preg_split('/\s+/u', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY) ?: array_filter([trim((string) $user->name)]);
    $publicName = $words ? $words[0].(count($words) > 1 ? ' '.mb_strtoupper(mb_substr(end($words), 0, 1)).'.' : '') : '';
    $deletionErrors = $errors->getBag('userDeletion')->any();
@endphp
<x-layouts.app title="Profile" :noindex="true" :flash-except="['status']">
    <div class="container-page py-section-sm">
        <x-account-header title="Profile"/>

        <div class="grid gap-12">
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8" aria-labelledby="details-h">
                <div class="grid content-start gap-2 lg:col-span-4">
                    <h2 class="h4" id="details-h">Profile details</h2>
                    <p class="text-sm text-muted">Your name appears on reviews as “{{ $publicName }}”.</p>
                </div>
                <form class="card grid gap-5 p-6 lg:col-span-7 lg:col-start-6" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <x-input name="name" label="Name" :value="$user->name" autocomplete="name" required maxlength="255"/>
                    <x-input name="email" label="Email" type="email" :value="$user->email" autocomplete="email" required/>
                    <div class="flex flex-wrap items-center gap-4">
                        <button class="btn btn-primary" type="submit" data-busy-label="Saving…">Save changes</button>
                        @if (session('status') === 'Saved.')
                            <p class="flex items-center gap-1.5 text-sm font-semibold text-success" role="status" data-fade-out="3000"><x-icon name="circle-check" class="icon-sm"/>Saved.</p>
                        @endif
                    </div>
                </form>
            </section>

            <hr class="rule">

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8" aria-labelledby="password-h">
                <div class="grid content-start gap-2 lg:col-span-4">
                    <h2 class="h4" id="password-h">Password</h2>
                    <p class="text-sm text-muted">Use at least 8 characters.</p>
                </div>
                <form class="card grid gap-5 p-6 lg:col-span-7 lg:col-start-6" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <x-input name="current_password" label="Current password" type="password" bag="updatePassword" autocomplete="current-password" required/>
                    <x-input name="password" label="New password" type="password" bag="updatePassword" autocomplete="new-password" required minlength="8"/>
                    <x-input name="password_confirmation" label="Confirm new password" type="password" bag="updatePassword" autocomplete="new-password" required/>
                    <div class="flex flex-wrap items-center gap-4">
                        <button class="btn btn-secondary" type="submit" data-busy-label="Updating…">Update password</button>
                        @if (session('status') === 'Password updated.')
                            <p class="flex items-center gap-1.5 text-sm font-semibold text-success" role="status" data-fade-out="3000"><x-icon name="circle-check" class="icon-sm"/>Password updated.</p>
                        @endif
                    </div>
                </form>
            </section>

            <hr class="rule">

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8" aria-labelledby="delete-h">
                <div class="grid content-start gap-2 lg:col-span-4">
                    <h2 class="h4" id="delete-h">Delete account</h2>
                    <p class="text-sm text-muted">Deleting your account removes your library and your reviews. We keep your order records for our accounts. This can’t be undone.</p>
                </div>
                <form class="card grid justify-items-start gap-4 p-6 lg:col-span-7 lg:col-start-6" method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    @if ($deletionErrors)
                        <x-alert variant="danger">{{ $errors->getBag('userDeletion')->first() }}</x-alert>
                    @endif
                    <button class="btn btn-danger-quiet js-only" type="button" data-dialog-open="delete-account" aria-haspopup="dialog">Delete account…</button>
                    <dialog class="dialog" id="delete-account" aria-labelledby="delete-account-title" aria-describedby="delete-account-desc" data-nojs-inline @if ($deletionErrors) data-open-on-load @endif>
                        <div class="dialog__body">
                            <h3 class="h3" id="delete-account-title">Delete your account?</h3>
                            <p class="text-muted" id="delete-account-desc">Your library and reviews go with it. Enter your password to confirm.</p>
                            <x-input name="password" label="Password" type="password" bag="userDeletion" autocomplete="current-password" required/>
                        </div>
                        <div class="dialog__actions">
                            <button class="btn btn-secondary js-only" type="button" data-dialog-close autofocus>Keep my account</button>
                            <button class="btn btn-danger" type="submit" data-busy-label="Deleting…">Delete account</button>
                        </div>
                    </dialog>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
