{{--
    Error summary for forms with more than three fields (DESIGN.md §4.5): takes focus on load
    and links to each invalid field. :fields maps an error key to its field id when they differ.
--}}
@props(['bag' => 'default', 'action' => 'save this form', 'fields' => [], 'except' => ['delete']])
@php
    $bagErrors = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($bag);
    $keys = collect($bagErrors->keys())->reject(fn ($k) => in_array($k, $except, true))->values();
    $n = $keys->count();
@endphp
@if ($n > 0)
    <div {{ $attributes->class('alert alert-danger') }} role="alert" tabindex="-1" data-error-summary>
        <x-icon name="circle-alert"/>
        <div class="grid gap-1">
            <p class="font-semibold">Fix {{ $n }} {{ \Illuminate\Support\Str::plural('field', $n) }} to {{ $action }}</p>
            <ul class="grid gap-1">
                @foreach ($keys as $key)
                    <li><a class="link" href="#{{ $fields[$key] ?? str_replace(['[', ']', '.', '_'], ['-', '', '-', '-'], $key) }}">{{ $bagErrors->first($key) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
