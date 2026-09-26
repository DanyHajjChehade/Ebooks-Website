{{-- Admin navigation (sidebar, drawer and no-JS fallback share it). --}}
@props(['brand' => true])
@php
    $groups = [
        'Overview' => [['Dashboard', 'admin.dashboard', 'layout-dashboard', 'admin.dashboard']],
        'Catalogue' => [
            ['Books', 'admin.books.index', 'book-open', 'admin.books.*'],
            ['Authors', 'admin.authors.index', 'feather', 'admin.authors.*'],
            ['Categories', 'admin.categories.index', 'tags', 'admin.categories.*'],
        ],
        'Sales' => [['Orders', 'admin.orders.index', 'receipt-text', 'admin.orders.*']],
        'People' => [
            ['Users', 'admin.users.index', 'users', 'admin.users.*'],
            ['Reviews', 'admin.reviews.index', 'message-square-quote', 'admin.reviews.*'],
        ],
        'Site' => [['Settings', 'admin.settings.edit', 'settings', 'admin.settings.*']],
    ];
    $user = auth()->user();
@endphp
<div class="flex min-h-full flex-1 flex-col">
    @if ($brand)
        <div class="mb-4 flex items-center gap-2 px-3">
            <x-wordmark class="wordmark--compact"/>
            <span class="badge badge-accent">Admin</span>
        </div>
    @endif
    <nav class="grid gap-0.5" aria-label="Admin">
        @foreach ($groups as $group => $links)
            <p class="eyebrow mb-1.5 mt-4 px-3 first:mt-0">{{ $group }}</p>
            @foreach ($links as [$label, $route, $icon, $pattern])
                <a class="side-link" href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif><x-icon :name="$icon"/>{{ $label }}</a>
            @endforeach
        @endforeach
    </nav>
    <div class="mt-auto grid gap-3 pt-6">
        <a class="side-link" href="{{ route('home') }}"><x-icon name="arrow-up-right"/>View shop</a>
        <hr class="rule">
        @if ($user)
            <div class="flex items-center gap-2 px-3">
                <x-avatar :name="$user->name" :id="$user->id" size="sm"/>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $user->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm btn-icon" type="submit" aria-label="Log out" title="Log out"><x-icon name="log-out"/></button>
                </form>
            </div>
        @endif
    </div>
</div>
