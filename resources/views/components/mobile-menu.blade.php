{{-- Full-screen mobile menu (DESIGN.md §4.2): native <dialog> opened with showModal(). --}}
@php $user = auth()->user(); @endphp
<dialog class="menu-sheet lg:hidden" id="mobile-menu" aria-label="Menu" data-menu-sheet>
    <div class="flex min-h-full flex-col">
        <div class="container-page flex items-center justify-between h-14 border-b border-line">
            <x-wordmark/>
            <button class="btn btn-ghost btn-icon" type="button" aria-label="Close menu" data-dialog-close autofocus><x-icon name="x"/></button>
        </div>
        <nav class="container-page grid gap-5 py-5" aria-label="Mobile">
            <div class="menu-sheet__big" data-menu-items>
                <a href="{{ route('books.index') }}" @if (request()->routeIs('books.*') && request('sort') !== 'newest') aria-current="page" @endif>Books</a>
                <a href="{{ route('authors.index') }}" @if (request()->routeIs('authors.*')) aria-current="page" @endif>Authors</a>
                <a href="{{ route('books.index', ['sort' => 'newest']) }}">New releases</a>
            </div>
            <hr class="rule">
            <div class="menu-sheet__small" data-menu-items>
                @if ($user)
                    <a href="{{ route('library.index') }}">Your library</a>
                    <a href="{{ route('orders.index') }}">Orders</a>
                    <a href="{{ route('profile.edit') }}">Profile</a>
                    @can('access-admin')<a href="{{ route('admin.dashboard') }}">Admin</a>@endcan
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}">Create account</a>
                @endif
            </div>
            <x-theme-switch class="justify-self-start"/>
        </nav>
        <div class="container-page mt-auto flex flex-wrap gap-x-4 gap-y-2 py-5 text-sm text-muted">
            <a class="link-quiet" href="{{ route('pages.contact') }}">Contact</a>
            <a class="link-quiet" href="{{ route('pages.refunds') }}">Refund policy</a>
            <a class="link-quiet" href="{{ route('pages.terms') }}">Terms</a>
            <a class="link-quiet" href="{{ route('pages.privacy') }}">Privacy</a>
        </div>
    </div>
</dialog>
