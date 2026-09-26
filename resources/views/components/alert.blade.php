{{-- Inline alert (DESIGN.md §4.13). variant: danger | success | warning | null (neutral). --}}
@props(['variant' => null, 'title' => null, 'icon' => null])
@php
    $icon ??= match ($variant) {
        'danger' => 'circle-alert',
        'success' => 'circle-check',
        'warning' => 'triangle-alert',
        default => 'info',
    };
    $role = match ($variant) {
        'danger' => 'alert',
        'success' => 'status',
        default => 'note',
    };
@endphp
<div {{ $attributes->class(['alert', 'alert-'.$variant => $variant]) }} role="{{ $role }}">
    <x-icon :name="$icon"/>
    <div class="grid gap-1 min-w-0">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        @if (trim($slot) !== '')<div>{{ $slot }}</div>@endif
    </div>
</div>
