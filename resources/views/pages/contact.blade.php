{{-- Contact (A5/A6): email, phone and address from settings. No form, so no spam surface. --}}
@php
    $email = $settings->contact_email;
    $phone = $settings->phone;
    $address = $settings->address;
    $social = $settings->socialLinks();
    $networks = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];
    $shop = $settings->site_name ?: 'Book Planet';
@endphp
<x-layouts.app title="Contact us" :description="'How to reach '.$shop.' about an order, a download or your account.'">
    <div class="container-page py-section-sm">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-8">
            <div class="grid content-start gap-6 lg:col-span-5">
                <h1 class="h1">Contact us</h1>
                <p class="lede">Questions about an order, a download or your account? Write to us, and include your order number if you have one.</p>
                <div class="grid gap-3">
                    <h2 class="eyebrow">Before you write</h2>
                    <p class="flex gap-3"><x-icon name="download" class="mt-0.5 text-muted"/><span>Download problems? Your files are in <a class="link" href="{{ route('library.index') }}">your library</a>.</span></p>
                    <p class="flex gap-3"><x-icon name="receipt-text" class="mt-0.5 text-muted"/><span>Refunds: read our <a class="link" href="{{ route('pages.refunds') }}">refund policy</a>.</span></p>
                </div>
            </div>
            <div class="lg:col-span-6 lg:col-start-7">
                @if (! $email && ! $phone && ! $address)
                    <div class="card p-6">
                        <p class="font-serif text-xl">Contact details are coming soon.</p>
                    </div>
                @else
                    <dl class="card dl-rows px-6 py-2">
                        @if ($email)
                            <div class="grid gap-1">
                                <dt class="flex items-center gap-2 text-sm text-muted"><x-icon name="mail" class="icon-sm"/>Email</dt>
                                <dd class="flex flex-wrap items-center justify-between gap-2">
                                    <a class="link break-all text-lg" href="mailto:{{ $email }}">{{ $email }}</a>
                                    <button class="btn btn-ghost btn-sm js-only" type="button" data-copy="{{ $email }}" aria-label="Copy email address"><x-icon name="copy"/><span data-copy-label>Copy</span></button>
                                </dd>
                            </div>
                        @endif
                        @if ($phone)
                            <div class="grid gap-1">
                                <dt class="flex items-center gap-2 text-sm text-muted"><x-icon name="phone" class="icon-sm"/>Phone</dt>
                                <dd><a class="link text-lg" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></dd>
                            </div>
                        @endif
                        @if ($address)
                            <div class="grid gap-1">
                                <dt class="flex items-center gap-2 text-sm text-muted"><x-icon name="map-pin" class="icon-sm"/>Postal address</dt>
                                <dd><address class="not-italic">{!! nl2br(e($address)) !!}</address></dd>
                            </div>
                        @endif
                    </dl>
                @endif
                @if ($social)
                    <div class="mt-4 flex flex-wrap gap-1">
                        @foreach ($social as $network => $url)
                            <a class="btn btn-ghost btn-icon" href="{{ $url }}" rel="noopener" target="_blank" aria-label="{{ $shop }} on {{ $networks[$network] ?? ucfirst($network) }}"><x-brand-icon :name="$network"/></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
