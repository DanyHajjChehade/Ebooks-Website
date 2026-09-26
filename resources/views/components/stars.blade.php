{{-- Rating display, rounded to the nearest half star. <x-stars :value="4.3" size="lg"/> --}}
@props(['value' => 0, 'size' => 'md'])
@php
    $v = round(((float) $value) * 2) / 2;
    $label = rtrim(rtrim(number_format($v, 1), '0'), '.').' out of 5 stars';
@endphp
<span {{ $attributes->class(['stars', 'stars--lg' => $size === 'lg']) }} role="img" aria-label="{{ $label }}">
    @for ($i = 1; $i <= 5; $i++)
        <span class="star {{ $v >= $i ? 'star--full' : ($v >= $i - 0.5 ? 'star--half' : 'star--empty') }}"><x-icon name="star" class="star__empty"/><x-icon name="star" class="star__fill"/></span>
    @endfor
</span>
