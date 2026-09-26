{{-- Checkbox row (the whole row is the label). <x-checkbox name="remember" label="Remember me"/> --}}
@props(['name', 'label', 'checked' => false, 'value' => '1', 'id' => null, 'description' => null])
@php
    $id ??= str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $name);
    $isChecked = old() ? (bool) old($name) : (bool) $checked;
@endphp
<label {{ $attributes->only('class')->class('check-row py-2') }} for="{{ $id }}">
    <input {{ $attributes->except('class') }} class="checkbox" type="checkbox" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" @checked($isChecked)>
    <span>{{ $label }}@if ($description)<span class="block text-sm text-muted">{{ $description }}</span>@endif</span>
</label>
