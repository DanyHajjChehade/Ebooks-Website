{{-- Account pages: h1 + Library · Orders · Profile link tabs (DESIGN.md §5.3). --}}
@props(['title', 'subtitle' => null])
<header {{ $attributes->class('mb-8 grid gap-4') }}>
    <div class="grid gap-1">
        <h1 class="h1">{{ $title }}</h1>
        @if ($subtitle)<p class="text-muted">{{ $subtitle }}</p>@endif
    </div>
    <nav class="tabs" aria-label="Account">
        <a class="tab" href="{{ route('library.index') }}" @if (request()->routeIs('library.*')) aria-current="page" @endif>Library</a>
        <a class="tab" href="{{ route('orders.index') }}" @if (request()->routeIs('orders.*')) aria-current="page" @endif>Orders</a>
        <a class="tab" href="{{ route('profile.edit') }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif>Profile</a>
    </nav>
</header>
