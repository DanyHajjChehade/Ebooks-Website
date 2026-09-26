{{-- Initials avatar (A9: no uploads). Authors may pass :photo. size: sm | md | lg | xl --}}
@props(['name' => '', 'id' => 0, 'size' => 'md', 'photo' => null])
@php
    // preg_split returns false on invalid UTF-8: fall back to the raw text.
    $words = preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: array_filter([trim((string) $name)]);
    $initials = '';
    if ($words) {
        $initials = mb_strtoupper(mb_substr($words[0], 0, 1));
        if (count($words) > 1) {
            $initials .= mb_strtoupper(mb_substr(end($words), 0, 1));
        }
    }
    $sizeClass = ['sm' => 'avatar-sm', 'md' => '', 'lg' => 'avatar-lg', 'xl' => 'avatar-xl'][$size] ?? '';
    $px = ['sm' => 32, 'md' => 40, 'lg' => 64, 'xl' => 96][$size] ?? 40;
@endphp
@if ($photo)
    <img {{ $attributes->class(['avatar', $sizeClass, 'object-cover']) }} src="{{ $photo }}" alt="" width="{{ $px }}" height="{{ $px }}" loading="lazy" decoding="async">
@else
    <span {{ $attributes->class(['avatar', $sizeClass]) }} data-swatch="{{ ((int) $id) % 10 }}" aria-hidden="true">{{ $initials }}</span>
@endif
