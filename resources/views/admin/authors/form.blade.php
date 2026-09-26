{{-- Admin author form. Fields: name, slug, bio, photo, remove_photo. --}}
@php
    $editing = $author->exists;
    $maxImageMb = rtrim(rtrim(number_format(config('bookplanet.max_image_kb') / 1024, 1), '0'), '.');
@endphp
<x-error-summary action="save this author"/>
<form class="grid max-w-form grid-cols-1 gap-6" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.authors.update', $author) : route('admin.authors.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif
    <section class="card grid gap-5 p-5 sm:p-6" aria-labelledby="author-details-h">
        <h2 class="h4" id="author-details-h">Details</h2>
        <x-input name="name" label="Name" :value="$author->name" required maxlength="255"/>
        <x-input name="slug" label="Slug" optional addon="/authors/" :value="$author->slug" maxlength="190" autocomplete="off" data-slug-from="name" hint="Used in the web address. Letters, numbers and dashes. Leave empty to make one from the name."/>
        <x-textarea name="bio" label="Bio" optional :value="$author->bio" rows="6" hint="Plain text. Leave a blank line between paragraphs."/>
    </section>
    <section class="card grid gap-4 p-5 sm:p-6" aria-labelledby="photo-h">
        <h2 class="h4" id="photo-h">Photo</h2>
        <div class="flex items-center gap-4">
            <x-avatar :name="$author->name ?: 'New author'" :id="$author->id" size="lg" :photo="$author->photo_url"/>
            <p class="text-sm text-muted">{{ $author->photo_path ? 'Current photo.' : 'No photo: the shop shows their initials.' }}</p>
        </div>
        @if ($author->photo_path)
            <x-checkbox name="remove_photo" label="Remove the photo"/>
        @endif
        <x-file-input name="photo" :label="$author->photo_path ? 'Replace the photo' : 'Photo'" optional kind="photo" accept="image/jpeg,image/png,image/webp" :constraint="'JPG, PNG or WebP · square images work best · up to '.$maxImageMb.' MB'"/>
    </section>
    <div class="form-actions">
        <x-button variant="ghost" :href="route('admin.authors.index')">Cancel</x-button>
        <button class="btn btn-primary" type="submit" data-busy-label="Saving…">{{ $editing ? 'Save changes' : 'Create author' }}</button>
    </div>
</form>
