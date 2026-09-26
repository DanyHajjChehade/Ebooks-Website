{{-- One toast (DESIGN.md §4.12). JS builds the same markup from <template id="tpl-toast">. --}}
@props(['type' => 'success', 'action' => null, 'actionLabel' => null])
@php
    $icon = ['success' => 'circle-check', 'error' => 'circle-alert', 'info' => 'info'][$type] ?? 'info';
@endphp
<div class="toast toast--{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" data-toast data-type="{{ $type }}">
    <x-icon :name="$icon"/>
    <div>{{ $slot }}@if ($action)<br><a class="toast__action" href="{{ $action }}">{{ $actionLabel }}</a>@endif</div>
    <button class="btn btn-ghost btn-icon btn-sm js-only" type="button" aria-label="Dismiss" data-toast-dismiss><x-icon name="x" class="icon-sm"/></button>
</div>
