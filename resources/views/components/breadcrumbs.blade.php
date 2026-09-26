{{--
    Breadcrumbs (DESIGN.md §4.15). :items = [['Books', url], ['Fiction', url], ['Mrs Dalloway']].
    The last item is the current page. Phones show only "‹ {parent}".
--}}
@props(['items' => []])
@php
    $items = array_values($items);
    $parent = count($items) > 1 ? $items[count($items) - 2] : null;
@endphp
<nav {{ $attributes->class('breadcrumbs') }} aria-label="Breadcrumb">
    <ol class="hidden sm:flex">
        @foreach ($items as $i => $item)
            <li>
                @if ($i === count($items) - 1 || empty($item[1]))
                    <span @if ($i === count($items) - 1) aria-current="page" @endif>{{ $item[0] }}</span>
                @else
                    <a href="{{ $item[1] }}">{{ $item[0] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
    @if ($parent && ! empty($parent[1]))
        <a class="sm:hidden inline-flex items-center gap-1 min-h-11 text-sm text-muted" href="{{ $parent[1] }}"><x-icon name="chevron-left" class="icon-sm"/>{{ $parent[0] }}</a>
    @endif
</nav>
