{{--
    <x-button variant="primary|secondary|ghost|danger-quiet|danger" size="sm|lg" icon="lock" trailing="arrow-right" href="…">Label</x-button>
    Renders <a> when href is set, otherwise <button type="button|submit">.
--}}
@props(['variant' => 'secondary', 'size' => null, 'icon' => null, 'trailing' => null, 'href' => null, 'type' => 'button', 'block' => false])
@php
    $classes = ['btn', 'btn-'.$variant, 'btn-'.$size => $size, 'btn-block' => $block];
@endphp
@if ($href)
    <a {{ $attributes->class($classes) }} href="{{ $href }}">@if ($icon)<x-icon :name="$icon"/>@endif{{ $slot }}@if ($trailing)<x-icon :name="$trailing"/>@endif</a>
@else
    <button {{ $attributes->class($classes) }} type="{{ $type }}">@if ($icon)<x-icon :name="$icon"/>@endif{{ $slot }}@if ($trailing)<x-icon :name="$trailing"/>@endif</button>
@endif
