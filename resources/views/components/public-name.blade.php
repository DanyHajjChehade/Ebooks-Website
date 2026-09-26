{{-- A reviewer's public name: first name + last initial ("Priya N."). --}}
@props(['name' => ''])
@php
    // preg_split returns false on invalid UTF-8: fall back to the raw text.
    $words = preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: array_filter([trim((string) $name)]);
    $public = $words ? $words[0].(count($words) > 1 ? ' '.mb_strtoupper(mb_substr(end($words), 0, 1)).'.' : '') : 'A reader';
@endphp
{{ $public }}
