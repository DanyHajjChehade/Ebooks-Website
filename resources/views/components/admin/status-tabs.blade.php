{{-- Admin status link tabs. :tabs = [[label, url, current(bool)], …] --}}
@props(['tabs' => [], 'label' => 'Status'])
<nav {{ $attributes->class('tabs') }} aria-label="{{ $label }}">
    @foreach ($tabs as [$text, $href, $current])
        <a class="tab" href="{{ $href }}" @if ($current) aria-current="page" @endif>{{ $text }}</a>
    @endforeach
</nav>
