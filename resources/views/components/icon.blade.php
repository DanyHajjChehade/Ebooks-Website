{{-- Lucide icon inlined from resources/icons (copied by `npm run icons`). <x-icon name="search" class="icon-sm"/> --}}
@props(['name'])
@php
    $inner = cache()->store('array')->rememberForever('bp.icon.'.$name, function () use ($name) {
        $svg = @file_get_contents(resource_path('icons/'.basename($name).'.svg'));

        return $svg === false ? '' : preg_replace(['/^.*?<svg[^>]*>/s', '/<\/svg>\s*$/'], '', $svg);
    });
@endphp
<svg {{ $attributes->class('icon') }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $inner !!}</svg>
