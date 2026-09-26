{{-- Admin category form. Fields: name, slug, description. --}}
@php $editing = $category->exists; @endphp
<form class="grid max-w-form grid-cols-1 gap-6" method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif
    <section class="card grid gap-5 p-5 sm:p-6" aria-labelledby="category-details-h">
        <h2 class="h4" id="category-details-h">Details</h2>
        <x-input name="name" label="Name" :value="$category->name" required maxlength="255"/>
        <x-input name="slug" label="Slug" optional addon="/categories/" :value="$category->slug" maxlength="190" autocomplete="off" data-slug-from="name" hint="Used in the web address. Letters, numbers and dashes. Leave empty to make one from the name."/>
        <x-textarea name="description" label="Description" optional :value="$category->description" rows="4" hint="Shown at the top of the shelf page."/>
    </section>
    <div class="form-actions">
        <x-button variant="ghost" :href="route('admin.categories.index')">Cancel</x-button>
        <button class="btn btn-primary" type="submit" data-busy-label="Saving…">{{ $editing ? 'Save changes' : 'Create category' }}</button>
    </div>
</form>
