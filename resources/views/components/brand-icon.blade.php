{{-- simple-icons brand mark (filled). <x-brand-icon name="instagram"/> --}}
@props(['name'])
@php
    $inner = cache()->store('array')->rememberForever('bp.brand.'.$name, function () use ($name) {
        $svg = @file_get_contents(resource_path('icons/brands/'.basename($name).'.svg'));

        return $svg === false ? '' : preg_replace(['/^.*?<svg[^>]*>/s', '/<\/svg>\s*$/'], '', $svg);
    });
@endphp
<svg {{ $attributes->class('size-5 fill-current') }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $inner !!}</svg>
