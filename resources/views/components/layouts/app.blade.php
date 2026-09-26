{{--
    Storefront + account layout: skip link → header → <main id="main"> → footer → toasts →
    live region → planet sprite. SEO via props; extra <head> tags via <x-slot:head>.
    <x-layouts.app title="All books" description="…" :noindex="true">…</x-layouts.app>
--}}
@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'noindex' => false,
    'flashExcept' => [],
])
@php
    $siteName = trim((string) ($settings->site_name ?? '')) ?: 'Book Planet';
    $fullTitle = $title ? $title.' · '.$siteName : $siteName.($settings->tagline ? ' · '.$settings->tagline : '');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-head :title="$fullTitle" :description="$description ?? $settings->tagline" :canonical="$canonical" :og-type="$ogType" :og-image="$ogImage" :noindex="$noindex" :site-name="$siteName">{{ $head ?? '' }}</x-head>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="absolute top-0 left-0 h-2 w-px" aria-hidden="true" data-scroll-sentinel></div>
    <x-planet-sprite/>
    <x-site-header/>
    <main id="main" tabindex="-1" class="outline-none">
        {{ $slot }}
    </main>
    <x-site-footer/>
    <x-mobile-menu/>
    <x-toast-region :except="$flashExcept"/>
    <x-confirm-dialog/>
    {{ $after ?? '' }}
</body>
</html>
