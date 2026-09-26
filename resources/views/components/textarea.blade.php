{{-- Labelled textarea. <x-textarea name="bio" label="Bio" optional rows="6" :value="$author->bio"/> --}}
@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'optional' => false,
    'bag' => 'default',
    'id' => null,
])
@php
    $id ??= ($bag !== 'default' ? \Illuminate\Support\Str::kebab($bag).'-' : '').str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $name);
    $error = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($bag)->first($name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp
<div {{ $attributes->only('class')->class('field') }}>
    <label class="label" for="{{ $id }}">{{ $label }}@if ($optional) <span class="optional">(optional)</span>@endif</label>
    <textarea {{ $attributes->except('class')->class('textarea') }} id="{{ $id }}" name="{{ $name }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($error) aria-invalid="true" @endif>{{ old($name, $value) }}</textarea>
    @if ($hint)<p class="hint" id="{{ $id }}-hint">{{ $hint }}</p>@endif
    @if ($error)<p class="field-error" id="{{ $id }}-error"><x-icon name="circle-alert"/>{{ $error }}</p>@endif
</div>
