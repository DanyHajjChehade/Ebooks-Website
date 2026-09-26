{{-- Order status badge: Paid (success) · Pending (warning) · Failed (danger) · Refunded (neutral) --}}
@props(['status'])
@php
    $variant = match ($status->value) {
        'paid' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        default => null,
    };
@endphp
<x-badge :variant="$variant" {{ $attributes }}>{{ $status->label() }}</x-badge>
