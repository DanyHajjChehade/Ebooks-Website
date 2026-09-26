{{--
    Standalone error layout (DESIGN.md §5.5). Must not touch the database, $settings, the
    session or the auth user: a 500/503 may be the database failing. SiteComposer skips every
    view whose name starts with "errors", and nothing here renders a component or reads the session.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.add('js');try{var t=localStorage.getItem('bp-theme');if(t==='light'||t==='dark')document.documentElement.dataset.theme=t}catch(e){}</script>
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#F6F1E7" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#151413" media="(prefers-color-scheme: dark)">
    <title>@yield('title') · Book Planet</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-dvh flex-col">
    <svg class="absolute size-0 overflow-hidden" aria-hidden="true" focusable="false">
        <defs>
            <mask id="bp-planet-cut" maskUnits="userSpaceOnUse" x="0" y="0" width="32" height="32">
                <rect width="32" height="32" fill="#fff"/>
                <path d="M3.21 21.69A14 4.5 -24 0 0 28.79 10.31" fill="none" stroke="#000" stroke-width="4.6"/>
            </mask>
        </defs>
        <symbol id="bp-planet" viewBox="0 0 32 32">
            <path d="M3.21 21.69A14 4.5 -24 0 1 28.79 10.31" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            <circle cx="16" cy="16" r="8.25" fill="currentColor" mask="url(#bp-planet-cut)"/>
            <path d="M3.21 21.69A14 4.5 -24 0 0 28.79 10.31" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </symbol>
    </svg>
    <a class="skip-link" href="#main">Skip to content</a>
    <main id="main" tabindex="-1" class="container-page flex-1 outline-none">
        <div class="mx-auto grid max-w-[36rem] grid-cols-1 justify-items-start gap-6 py-[12vh]">
            <a class="wordmark" href="{{ url('/') }}" aria-label="Book Planet home"><svg class="wordmark__mark" aria-hidden="true" focusable="false"><use href="#bp-planet"/></svg><span>Book <em>Planet</em></span></a>
            <p class="font-serif text-5xl font-light italic text-muted" aria-hidden="true">@yield('code')</p>
            <h1 class="h1">@yield('heading')</h1>
            <p class="lede">@yield('body')</p>
            @hasSection('actions')
                <div class="flex flex-wrap items-center gap-3">@yield('actions')</div>
            @endif
        </div>
    </main>
    <footer class="container-page pb-8 text-xs text-muted">© Book Planet</footer>
</body>
</html>
