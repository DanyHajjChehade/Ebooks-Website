{{--
    Shared <head> contents: theme script first (no flash), SEO + Open Graph/Twitter tags,
    icons, font preloads and the Vite bundle. The default slot adds page-specific tags (JSON-LD).
--}}
@props([
    'title',
    'description' => null,
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'noindex' => false,
    'siteName' => 'Book Planet',
])
@php
    // preg_replace returns null on invalid UTF-8: keep the raw text rather than failing.
    $description = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $description) ?? (string) $description), 160);
    // Default canonical: this URL without its query string, except ?page= when it is past page 1,
    // so paginated listings don't all point at page 1 and filter/sort variants collapse together.
    if ($canonical === null) {
        $page = filter_var(request()->query('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $canonical = url()->current().($page > 1 ? '?page='.$page : '');
    }
    $ogImage ??= asset('og-default.png');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.classList.add('js');try{var t=localStorage.getItem('bp-theme');if(t==='light'||t==='dark')document.documentElement.dataset.theme=t}catch(e){}</script>
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#F6F1E7" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#151413" media="(prefers-color-scheme: dark)">
<title>{{ $title }}</title>
@if ($description !== '')<meta name="description" content="{{ $description }}">@endif
<link rel="canonical" href="{{ $canonical }}">
@if ($noindex)<meta name="robots" content="noindex, nofollow">@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
@if ($description !== '')<meta property="og:description" content="{{ $description }}">@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
@if ($ogImage === asset('og-default.png'))
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $siteName }}: independent ebooks">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
@if ($description !== '')<meta name="twitter:description" content="{{ $description }}">@endif
<meta name="twitter:image" content="{{ $ogImage }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preload" as="font" type="font/woff2" crossorigin href="{{ \Illuminate\Support\Facades\Vite::asset('node_modules/@fontsource-variable/newsreader/files/newsreader-latin-opsz-normal.woff2') }}">
<link rel="preload" as="font" type="font/woff2" crossorigin href="{{ \Illuminate\Support\Facades\Vite::asset('node_modules/@fontsource-variable/schibsted-grotesk/files/schibsted-grotesk-latin-wght-normal.woff2') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
{{ $slot }}
