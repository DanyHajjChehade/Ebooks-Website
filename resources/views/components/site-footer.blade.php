{{-- Storefront footer (DESIGN.md §4.3). Help links are required (A5). --}}
@php
    $siteName = trim((string) ($settings->site_name ?? '')) ?: 'Book Planet';
    $networks = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];
@endphp
<footer class="bg-sunken border-t border-line" id="site-footer">
    <div class="container-page grid grid-cols-1 gap-8 py-10 sm:grid-cols-2 lg:grid-cols-4 lg:py-14">
        <div class="grid gap-3 content-start">
            <x-wordmark class="justify-self-start"/>
            @if ($settings->tagline)<p class="text-sm text-muted max-w-60">{{ $settings->tagline }}</p>@endif
            @if ($social = $settings->socialLinks())
                <div class="flex flex-wrap gap-1 -ml-2">
                    @foreach ($social as $network => $url)
                        <a class="btn btn-ghost btn-icon" href="{{ $url }}" rel="noopener" target="_blank" aria-label="{{ $siteName }} on {{ $networks[$network] ?? ucfirst($network) }}"><x-brand-icon :name="$network"/></a>
                    @endforeach
                </div>
            @endif
        </div>
        <nav class="grid gap-1 content-start text-sm" aria-labelledby="footer-shop">
            <h2 class="eyebrow mb-1" id="footer-shop">Shop</h2>
            <a class="link-quiet py-1" href="{{ route('books.index') }}">All books</a>
            <a class="link-quiet py-1" href="{{ route('authors.index') }}">Authors</a>
            <a class="link-quiet py-1" href="{{ route('books.index', ['sort' => 'newest']) }}">New releases</a>
            <a class="link-quiet py-1" href="{{ route('cart.index') }}">Cart</a>
        </nav>
        <nav class="grid gap-1 content-start text-sm" aria-labelledby="footer-account">
            <h2 class="eyebrow mb-1" id="footer-account">Account</h2>
            @auth
                <a class="link-quiet py-1" href="{{ route('library.index') }}">Your library</a>
                <a class="link-quiet py-1" href="{{ route('orders.index') }}">Orders</a>
                <a class="link-quiet py-1" href="{{ route('profile.edit') }}">Profile</a>
                @can('access-admin')<a class="link-quiet py-1" href="{{ route('admin.dashboard') }}">Admin</a>@endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="link-quiet py-1 cursor-pointer" type="submit">Log out</button>
                </form>
            @else
                <a class="link-quiet py-1" href="{{ route('login') }}">Log in</a>
                <a class="link-quiet py-1" href="{{ route('register') }}">Create account</a>
            @endauth
        </nav>
        <nav class="grid gap-1 content-start text-sm" aria-labelledby="footer-help">
            <h2 class="eyebrow mb-1" id="footer-help">Help</h2>
            <a class="link-quiet py-1" href="{{ route('pages.contact') }}">Contact</a>
            <a class="link-quiet py-1" href="{{ route('pages.refunds') }}">Refund policy</a>
            <a class="link-quiet py-1" href="{{ route('pages.terms') }}">Terms of service</a>
            <a class="link-quiet py-1" href="{{ route('pages.privacy') }}">Privacy policy</a>
        </nav>
    </div>
    <div class="border-t border-line">
        <div class="container-page flex flex-wrap items-center justify-between gap-3 py-4 text-xs text-muted">
            <p>© {{ now()->year }} {{ $siteName }} · Payments by Stripe · Set in Newsreader and Schibsted Grotesk</p>
            <x-theme-switch/>
        </div>
    </div>
</footer>
