{{-- <x-badge variant="success|warning|danger|accent|outline">Paid</x-badge> --}}
@props(['variant' => null])
<span {{ $attributes->class(['badge', 'badge-'.$variant => $variant]) }}>{{ $slot }}</span>
