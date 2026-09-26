{{-- Empty state (DESIGN.md §4.17). icon: a lucide name, or null for the planet mark. --}}
@props(['heading', 'icon' => null, 'level' => 2])
@php $tag = 'h'.max(2, min(4, (int) $level)); @endphp
<div {{ $attributes->class('empty-state px-4') }}>
    @if ($icon)
        <x-icon :name="$icon"/>
    @else
        <svg aria-hidden="true" focusable="false"><use href="#bp-planet"/></svg>
    @endif
    <{{ $tag }} class="h3">{{ $heading }}</{{ $tag }}>
    @if (trim($slot) !== '')<p>{{ $slot }}</p>@endif
    @isset($actions)<div class="empty-state__actions">{{ $actions }}</div>@endisset
</div>
