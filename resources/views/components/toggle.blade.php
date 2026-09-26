{{-- Switch with a visible label and one-line description. Absent = false on the server. --}}
@props(['name', 'label', 'description' => null, 'checked' => false, 'id' => null])
@php
    $id ??= str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $name);
    $isChecked = old() ? (bool) old($name) : (bool) $checked;
@endphp
<label {{ $attributes->only('class')->class('flex items-center justify-between gap-4 py-2 cursor-pointer') }} for="{{ $id }}">
    <span>
        <span class="block font-semibold">{{ $label }}</span>
        @if ($description)<span class="block text-sm text-muted" id="{{ $id }}-desc">{{ $description }}</span>@endif
    </span>
    <input {{ $attributes->except('class') }} class="switch" type="checkbox" role="switch" id="{{ $id }}" name="{{ $name }}" value="1" @checked($isChecked) @if ($description) aria-describedby="{{ $id }}-desc" @endif>
</label>
