{{-- Brand wordmark linking home. The last word of the site name is set in italic ("Book <em>Planet</em>"). --}}
@php
    $name = trim((string) ($settings->site_name ?? '')) ?: 'Book Planet';
    $words = preg_split('/\s+/u', $name);
    $last = count($words) > 1 ? array_pop($words) : null;
@endphp
<a {{ $attributes->class('wordmark') }} href="{{ route('home') }}" aria-label="{{ $name }} home">
    <svg class="wordmark__mark" aria-hidden="true" focusable="false"><use href="#bp-planet"/></svg>
    <span>{{ implode(' ', $words) }}@if ($last) <em>{{ $last }}</em>@endif</span>
</a>
