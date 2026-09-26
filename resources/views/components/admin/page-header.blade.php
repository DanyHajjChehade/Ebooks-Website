{{-- Admin page header: breadcrumbs (nested pages) + h1 styled .h2 + primary action (full width on phones). --}}
@props(['title', 'breadcrumbs' => null, 'subtitle' => null])
<div {{ $attributes->class('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="grid min-w-0 gap-1">
        @if ($breadcrumbs)<x-breadcrumbs :items="$breadcrumbs"/>@endif
        <h1 class="h2 break-words">{{ $title }}</h1>
        @if ($subtitle)<p class="text-sm text-muted">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 max-sm:*:w-full">{{ $actions }}</div>
    @endisset
</div>
