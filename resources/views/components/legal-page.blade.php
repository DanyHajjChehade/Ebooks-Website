{{--
    Long-form legal page (DESIGN.md §5.1, A5): header, placeholder notice, sticky "On this page"
    TOC (details on phones), numbered sections. :sections = [[id, heading, [paragraph, …]], …]
    A paragraph may be an array of strings, rendered as a bulleted list.
--}}
@props(['title', 'eyebrow' => 'Legal', 'updated', 'noun', 'description', 'sections' => []])
@php
    $toc = collect($sections)->values()->map(fn ($s, $i) => [$s[0], ($i + 1).'. '.$s[1]]);
@endphp
<x-layouts.app :title="$title" :description="$description">
    <div class="container-page py-section-sm">
        <x-breadcrumbs class="mb-6" :items="[[$eyebrow, null], [$title]]"/>
        <header class="mb-8 grid gap-3">
            <p class="eyebrow">{{ $eyebrow }}</p>
            <h1 class="h1">{{ $title }}</h1>
            <p class="text-sm text-muted">Last updated <time datetime="{{ \Illuminate\Support\Carbon::parse($updated)->toDateString() }}">{{ \Illuminate\Support\Carbon::parse($updated)->format('M j, Y') }}</time></p>
        </header>
        <x-alert class="mb-10 max-w-prose" variant="warning" title="Placeholder text">The shop owner must replace this page with their own {{ $noun }} before launch.</x-alert>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <aside class="lg:col-span-3" aria-label="On this page">
                <details class="card lg:hidden">
                    <summary class="flex min-h-11 items-center justify-between px-4 font-semibold">On this page<x-icon name="chevron-down" class="icon-sm"/></summary>
                    <nav class="toc px-4 pb-4" aria-label="Sections">
                        @foreach ($toc as [$id, $label])<a href="#{{ $id }}">{{ $label }}</a>@endforeach
                    </nav>
                </details>
                <div class="hidden lg:sticky lg:top-[calc(var(--spacing-header)+2rem)] lg:block">
                    <p class="eyebrow mb-3">On this page</p>
                    <nav class="toc" aria-label="Sections" data-toc>
                        @foreach ($toc as [$id, $label])<a href="#{{ $id }}">{{ $label }}</a>@endforeach
                    </nav>
                </div>
            </aside>
            <div class="min-w-0 lg:col-span-7 lg:col-start-5">
                {{ $slot }}
                <div class="prose-book prose-legal" data-toc-target>
                    @foreach (collect($sections)->values() as $i => [$id, $heading, $paragraphs])
                        <h2 id="{{ $id }}">{{ $i + 1 }}. {{ $heading }}</h2>
                        @foreach ($paragraphs as $p)
                            @if (is_array($p))
                                <ul>@foreach ($p as $item)<li>{{ $item }}</li>@endforeach</ul>
                            @else
                                <p>{{ $p }}</p>
                            @endif
                        @endforeach
                    @endforeach
                </div>
                <p class="mt-12 text-muted">Questions? <a class="link" href="{{ route('pages.contact') }}">Contact us</a>.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
