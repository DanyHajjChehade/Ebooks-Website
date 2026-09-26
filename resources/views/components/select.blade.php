{{--
    Labelled select. :options = [value => label]; placeholder adds an empty first option.
    <x-select name="author_id" label="Author" :options="$opts" :selected="$book->author_id" placeholder="Choose an author" required/>
--}}
@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'hint' => null,
    'optional' => false,
    'bag' => 'default',
    'id' => null,
    'hideLabel' => false,
    'labelClass' => null,
])
@php
    $id ??= ($bag !== 'default' ? \Illuminate\Support\Str::kebab($bag).'-' : '').str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $name);
    $error = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($bag)->first($name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $current = (string) old($name, $selected);
@endphp
<div {{ $attributes->only('class')->class('field') }}>
    <label @class(['label', 'sr-only' => $hideLabel, $labelClass => $labelClass]) for="{{ $id }}">{{ $label }}@if ($optional) <span class="optional">(optional)</span>@endif</label>
    <select {{ $attributes->except('class')->class('select') }} id="{{ $id }}" name="{{ $name }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($error) aria-invalid="true" @endif>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected($current === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
    @if ($hint)<p class="hint" id="{{ $id }}-hint">{{ $hint }}</p>@endif
    @if ($error)<p class="field-error" id="{{ $id }}-error"><x-icon name="circle-alert"/>{{ $error }}</p>@endif
</div>
