{{-- Admin filter bar (GET, role=search): search + optional selects/hidden inputs (slot) + Search + Clear. --}}
@props(['action', 'placeholder', 'label' => 'Search', 'q' => null, 'active' => false])
<form {{ $attributes->class('flex flex-wrap items-end gap-3') }} data-strip-empty method="GET" action="{{ $action }}" role="search">
    <label class="relative min-w-0 max-w-sm flex-1 basis-56">
        <span class="sr-only">{{ $label }}</span>
        <x-icon name="search" class="icon-sm pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted"/>
        <input class="input pl-9" type="search" name="q" value="{{ $q }}" placeholder="{{ $placeholder }}" autocomplete="off" maxlength="100">
    </label>
    {{ $slot }}
    <button class="btn btn-secondary" type="submit">Search</button>
    @if ($active)<a class="btn btn-ghost" href="{{ $action }}">Clear</a>@endif
</form>
