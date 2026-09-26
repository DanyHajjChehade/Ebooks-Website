{{-- Storefront header (DESIGN.md §4.1). Sticky; the border appears once scrolled (motion.js). --}}
@php
    $sortNewest = request()->routeIs('books.index') && request('sort') === 'newest';
    $nav = [
        ['Books', route('books.index'), request()->routeIs('books.*', 'categories.show') && ! $sortNewest],
        ['Authors', route('authors.index'), request()->routeIs('authors.*')],
        ['New releases', route('books.index', ['sort' => 'newest']), $sortNewest],
    ];
    $cartLabel = 'Cart, '.$cartCount.' '.\Illuminate\Support\Str::plural('book', $cartCount);
    $user = auth()->user();
@endphp
<header class="site-header" data-site-header>
    <div class="container-page site-header__bar">
        <x-wordmark/>

        <nav class="hidden lg:flex items-center gap-7" aria-label="Main">
            @foreach ($nav as [$label, $href, $current])
                <a class="nav-link" href="{{ $href }}" @if ($current) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-1 lg:gap-2">
            <form class="header-search hidden lg:flex items-center" action="{{ route('books.index') }}" method="GET" role="search" data-header-search>
                <a class="btn btn-ghost btn-icon header-search__toggle" href="{{ route('books.index') }}" aria-label="Search" data-header-search-toggle><x-icon name="search"/></a>
                <label class="header-search__field relative block">
                    <span class="sr-only">Search books and authors</span>
                    <x-icon name="search" class="icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none"/>
                    <input class="input min-h-10 py-2 pl-9 pr-2" type="search" name="q" placeholder="Search books and authors" autocomplete="off" value="{{ request()->routeIs('books.index') ? request('q') : '' }}" data-header-search-input>
                </label>
            </form>
            <a class="btn btn-ghost btn-icon lg:hidden" href="{{ route('books.index') }}" aria-label="Search" aria-controls="mobile-search" data-mobile-search-toggle><x-icon name="search"/></a>

            <button class="btn btn-ghost btn-icon hidden lg:inline-flex js-only" type="button" aria-label="Night reading" aria-pressed="false" data-theme-toggle><x-icon name="moon" class="theme-icon-moon"/><x-icon name="sun" class="theme-icon-sun"/></button>

            @if ($user)
                <details class="menu hidden lg:block" data-menu>
                    <summary class="btn btn-ghost gap-1.5 px-2" aria-label="Account menu"><x-avatar :name="$user->name" :id="$user->id" size="sm"/><x-icon name="chevron-down" class="icon-sm"/></summary>
                    <div class="menu__panel">
                        <div class="px-3 pt-2 pb-3 mb-1 border-b border-line">
                            <p class="font-semibold truncate">{{ $user->name }}</p>
                            <p class="text-sm text-muted truncate">{{ $user->email }}</p>
                        </div>
                        <a class="menu__item" href="{{ route('library.index') }}"><x-icon name="library-big"/>Your library</a>
                        <a class="menu__item" href="{{ route('orders.index') }}"><x-icon name="receipt-text"/>Orders</a>
                        <a class="menu__item" href="{{ route('profile.edit') }}"><x-icon name="user"/>Profile</a>
                        @can('access-admin')
                            <a class="menu__item" href="{{ route('admin.dashboard') }}"><x-icon name="layout-dashboard"/>Admin</a>
                        @endcan
                        <hr class="rule my-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="menu__item" type="submit"><x-icon name="log-out"/>Log out</button>
                        </form>
                    </div>
                </details>
            @else
                <a class="nav-link hidden lg:inline-flex px-2" href="{{ route('login') }}" @if (request()->routeIs('login')) aria-current="page" @endif>Log in</a>
                <a class="btn btn-secondary btn-sm hidden xl:inline-flex" href="{{ route('register') }}">Create account</a>
            @endif

            <a class="btn btn-secondary hidden sm:inline-flex gap-2 px-3" href="{{ route('cart.index') }}" aria-label="{{ $cartLabel }}" data-cart-link @if (request()->routeIs('cart.index')) aria-current="page" @endif>
                <x-icon name="shopping-bag" data-cart-icon/><span>Cart</span><span class="cart-count" data-cart-count @if ($cartCount === 0) hidden @endif>{{ $cartCount }}</span>
            </a>
            <a class="btn btn-ghost btn-icon relative sm:hidden" href="{{ route('cart.index') }}" aria-label="{{ $cartLabel }}" data-cart-link>
                <x-icon name="shopping-bag" data-cart-icon/><span class="cart-count absolute top-1 right-0.5" data-cart-count @if ($cartCount === 0) hidden @endif>{{ $cartCount }}</span>
            </a>

            <a class="btn btn-ghost btn-icon lg:hidden" href="#site-footer" aria-label="Menu" aria-controls="mobile-menu" aria-expanded="false" data-menu-open><x-icon name="menu"/></a>
        </div>
    </div>

    <div class="lg:hidden border-t border-line" id="mobile-search" hidden data-mobile-search>
        <form class="container-page flex items-center gap-2 py-3" action="{{ route('books.index') }}" method="GET" role="search">
            <label class="relative flex-1 min-w-0">
                <span class="sr-only">Search books and authors</span>
                <x-icon name="search" class="icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none"/>
                <input class="input pl-9" type="search" name="q" placeholder="Search books and authors" autocomplete="off" value="{{ request()->routeIs('books.index') ? request('q') : '' }}">
            </label>
            <button class="btn btn-ghost" type="button" data-mobile-search-close>Close</button>
        </form>
    </div>
</header>
