{{-- Home section header: eyebrow + h2 on the left, "See all →" on the right. --}}
@props(['id', 'title', 'eyebrow' => null, 'href' => null, 'link' => 'See all'])
<div {{ $attributes->class('mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-3 lg:mb-10') }} data-reveal>
    <div class="grid gap-2">
        @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h2 class="h2" id="{{ $id }}">{{ $title }}</h2>
    </div>
    @if ($href)
        <a class="link inline-flex min-h-11 items-center gap-1 text-sm font-semibold" href="{{ $href }}">{{ $link }}<x-icon name="arrow-right" class="icon-sm"/></a>
    @endif
</div>
