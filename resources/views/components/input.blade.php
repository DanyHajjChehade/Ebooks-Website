{{--
    Labelled text field: label → control → hint → error (DESIGN.md §4.5).
    <x-input name="email" label="Email" type="email" autocomplete="email" required/>
    Props: bag (error bag), hint, optional, addon ("$", "/books/"), input-class. type="password" adds Show/Hide.
    Other attributes go to the <input>; `class` goes to the wrapper.
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'optional' => false,
    'bag' => 'default',
    'id' => null,
    'addon' => null,
    'addonBrand' => null,
    'inputClass' => null,
])
@php
    $id ??= ($bag !== 'default' ? \Illuminate\Support\Str::kebab($bag).'-' : '').str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $name);
    $error = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($bag)->first($name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $isPassword = $type === 'password';
    $current = $isPassword ? null : old($name, $value);
@endphp
<div {{ $attributes->only('class')->class('field') }}>
    <label class="label" for="{{ $id }}">{{ $label }}@if ($optional) <span class="optional">(optional)</span>@endif</label>
    @if ($addon || $addonBrand)<div class="input-group"><span class="input-addon" aria-hidden="true">@if ($addonBrand)<x-brand-icon :name="$addonBrand" class="size-4"/>@else{{ $addon }}@endif</span>@endif
    @if ($isPassword)<div class="relative">@endif
    <input {{ $attributes->except('class')->class(['input', 'pr-18' => $isPassword, $inputClass => $inputClass]) }}
        id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        @if (! $isPassword && $current !== null) value="{{ $current }}" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($error) aria-invalid="true" @endif>
    @if ($isPassword)
        <button class="btn btn-ghost btn-sm absolute right-1 top-1/2 -translate-y-1/2 js-only" type="button" data-password-toggle aria-controls="{{ $id }}" aria-pressed="false" aria-label="Show password">Show</button>
    </div>
    @endif
    @if ($addon || $addonBrand)</div>@endif
    @if ($hint)<p class="hint" id="{{ $id }}-hint">{{ $hint }}</p>@endif
    @if ($error)<p class="field-error" id="{{ $id }}-error"><x-icon name="circle-alert"/>{{ $error }}</p>@endif
</div>
