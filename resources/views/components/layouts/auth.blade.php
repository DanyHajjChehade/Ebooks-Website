{{-- Auth layout (DESIGN.md §5.2): wordmark bar, a narrow column on paper, legal links. --}}
@props(['title', 'description' => null])
@php
    $siteName = trim((string) ($settings->site_name ?? '')) ?: 'Book Planet';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-head :title="$title.' · '.$siteName" :description="$description ?? $settings->tagline" :noindex="true" :site-name="$siteName"/>
</head>
<body class="flex min-h-dvh flex-col">
    <a class="skip-link" href="#main">Skip to content</a>
    <x-planet-sprite/>
    <header class="container-page flex h-14 items-center justify-between gap-4">
        <x-wordmark/>
        <a class="link-quiet text-sm" href="{{ route('home') }}">Back to the shop</a>
    </header>
    <main id="main" tabindex="-1" class="container-page flex-1 py-8 outline-none sm:py-12">
        <div class="mx-auto grid w-full max-w-narrow grid-cols-1 gap-6">
            {{ $slot }}
        </div>
    </main>
    <footer class="container-page pb-8 text-center text-xs text-muted">
        <a class="link-quiet" href="{{ route('pages.terms') }}">Terms</a> · <a class="link-quiet" href="{{ route('pages.privacy') }}">Privacy</a>
    </footer>
    <x-toast-region :except="['status']"/>
</body>
</html>
