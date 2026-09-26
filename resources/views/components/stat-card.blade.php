{{-- Admin stat (DESIGN.md §4.20): label, tabular value, hint. No deltas. --}}
@props(['label', 'value', 'hint' => null])
<div {{ $attributes->class('stat') }}>
    <p class="stat__label">{{ $label }}</p>
    <p class="stat__value">{{ $value }}</p>
    @if ($hint)<p class="stat__hint">{{ $hint }}</p>@endif
</div>
