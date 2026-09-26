{{--
    File dropzone (DESIGN.md §4.5): a label wrapping a transparent full-size input, so it is
    keyboard- and drop-accessible without JS. JS (forms.js) adds dragover state and the
    "selected" row / image preview. kind: file (ebook) | cover (2:3 preview) | photo (round preview).
    Slot `current` renders what is stored today (file row or image).
--}}
@props([
    'name',
    'label',
    'accept' => null,
    'constraint' => null,
    'kind' => 'file',
    'optional' => false,
    'bag' => 'default',
    'id' => null,
    'current' => null,
    'prompt' => null,
])
@php
    $id ??= str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $name);
    $error = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($bag)->first($name);
    $describedBy = trim(($constraint ? $id.'-constraint ' : '').($error ? $id.'-error' : ''));
@endphp
<div {{ $attributes->only('class')->class('field') }} data-file-field data-kind="{{ $kind }}">
    <span class="label" id="{{ $id }}-label">{{ $label }}@if ($optional) <span class="optional">(optional)</span>@endif</span>
    {{ $current ?? '' }}
    <label class="dropzone" data-dropzone @if ($error) aria-invalid="true" @endif>
        <input {{ $attributes->except('class') }} class="dropzone__input" type="file" id="{{ $id }}" name="{{ $name }}"
            @if ($accept) accept="{{ $accept }}" @endif
            aria-labelledby="{{ $id }}-label"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($error) aria-invalid="true" @endif>
        <x-icon :name="$kind === 'file' ? 'upload' : 'image-up'"/>
        <span data-dropzone-prompt>{!! $prompt ?? '<strong>Choose a file</strong> or drop it here' !!}</span>
        @if ($constraint)<span id="{{ $id }}-constraint">{{ $constraint }}</span>@endif
    </label>
    <div class="card flex items-center gap-3 p-3" data-file-selected hidden>
        @if ($kind === 'file')
            <span class="inline-grid place-items-center size-10 rounded-sm bg-sunken flex-none"><x-icon name="file-text"/></span>
        @else
            <img @class(['flex-none object-cover bg-sunken', 'w-16 aspect-[2/3] rounded-xs' => $kind === 'cover', 'size-16 rounded-full' => $kind === 'photo']) src="data:," alt="" data-file-preview>
        @endif
        <span class="min-w-0 flex-1">
            <span class="block font-semibold truncate" data-file-name></span>
            <span class="block text-sm text-muted" data-file-meta></span>
        </span>
        <button class="btn btn-ghost btn-sm" type="button" data-file-replace aria-controls="{{ $id }}">Replace</button>
    </div>
    @if ($error)<p class="field-error" id="{{ $id }}-error"><x-icon name="circle-alert"/>{{ $error }}</p>@endif
</div>
