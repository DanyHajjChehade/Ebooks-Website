{{-- Admin shell (DESIGN.md §4.20): sidebar from 1024px, top bar + left drawer below. --}}
@props(['title', 'flashExcept' => []])
@php
    $siteName = trim((string) ($settings->site_name ?? '')) ?: 'Book Planet';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-head :title="$title.' · Admin · '.$siteName" :description="'Admin · '.$siteName" :noindex="true" :site-name="$siteName"/>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <x-planet-sprite/>
    <div class="admin-shell">
        <aside class="admin-side">
            <div class="admin-side__inner">
                <x-admin.sidebar/>
            </div>
        </aside>
        <div class="min-w-0">
            <header class="sticky top-0 z-40 border-b border-line bg-bg lg:hidden">
                <div class="flex h-14 items-center gap-2 px-2 sm:px-4">
                    <a class="btn btn-ghost btn-icon" href="#admin-nav" aria-label="Admin menu" aria-controls="admin-drawer" aria-expanded="false" data-drawer-open><x-icon name="menu"/></a>
                    <p class="min-w-0 flex-1 truncate font-semibold">{{ $title }}</p>
                    <a class="btn btn-ghost btn-icon" href="{{ route('home') }}" aria-label="View shop"><x-icon name="arrow-up-right"/></a>
                </div>
            </header>
            <main id="main" tabindex="-1" class="mx-auto grid w-full max-w-admin grid-cols-1 gap-6 px-gutter py-6 outline-none lg:px-8 lg:py-8">
                {{ $slot }}
            </main>
            <div class="nojs-only border-t border-line px-gutter py-6 lg:hidden" id="admin-nav">
                <x-admin.sidebar :brand="false"/>
            </div>
        </div>
    </div>
    <dialog class="drawer-left lg:hidden" id="admin-drawer" aria-label="Admin menu" data-drawer>
        <div class="flex h-full flex-col px-3 py-5">
            <div class="mb-2 flex justify-end">
                <button class="btn btn-ghost btn-icon" type="button" aria-label="Close menu" data-dialog-close autofocus><x-icon name="x"/></button>
            </div>
            <x-admin.sidebar/>
        </div>
    </dialog>
    <x-toast-region :except="$flashExcept"/>
    <x-confirm-dialog/>
</body>
</html>
