{{-- Admin settings (DESIGN.md §5.4, A6). Data: $setting (singular; the row or unsaved defaults). --}}
<x-layouts.admin title="Settings">
    <x-admin.page-header title="Settings" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Settings']]"/>
    <x-error-summary action="save the settings"/>
    <form class="grid gap-12" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8" aria-labelledby="shop-h">
            <div class="grid content-start gap-2 lg:col-span-4">
                <h2 class="h4" id="shop-h">Shop details</h2>
                <p class="text-sm text-muted">Shown across the shop and on the contact page. The brand mark is fixed.</p>
            </div>
            <div class="card grid gap-5 p-5 sm:p-6 lg:col-span-8">
                <x-input name="site_name" label="Site name" :value="$setting->site_name" required maxlength="255"/>
                <x-input name="tagline" label="Tagline" optional :value="$setting->tagline" maxlength="255" hint="Shown on the home page and in search results."/>
                <x-input name="contact_email" label="Contact email" optional type="email" :value="$setting->contact_email" autocomplete="email" hint="Shown on the Contact page."/>
                <x-input name="phone" label="Phone" optional type="tel" :value="$setting->phone" autocomplete="tel"/>
                <x-textarea name="address" label="Postal address" optional :value="$setting->address" rows="3" autocomplete="street-address"/>
            </div>
        </section>

        <hr class="rule">

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8" aria-labelledby="social-h">
            <div class="grid content-start gap-2 lg:col-span-4">
                <h2 class="h4" id="social-h">Social links</h2>
                <p class="text-sm text-muted">Leave empty to hide. Links must start with https://.</p>
            </div>
            <div class="card grid gap-5 p-5 sm:p-6 lg:col-span-8">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $network => $label)
                    <x-input :name="$network.'_url'" :label="$label" optional type="url" :addon-brand="$network" :value="$setting->{$network.'_url'}" placeholder="https://" autocomplete="url"/>
                @endforeach
            </div>
        </section>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit" data-busy-label="Saving…">Save settings</button>
        </div>
    </form>
</x-layouts.admin>
