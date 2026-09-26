{{--
    Admin book form (DESIGN.md §5.4 admin.books.create/edit). Data: $book (new Book on create), $authors, $categories.
    Field names follow BookRequest: price / sale_price as decimals, is_published / is_featured checkboxes,
    cover + remove_cover, ebook (required on create).
--}}
@use('App\Support\Money')
@use('Illuminate\Support\Number')
@php
    $editing = $book->exists;
    $currency = Money::currency();
    $symbol = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'CAD' => '$', 'AUD' => '$', 'NZD' => '$'][$currency] ?? $currency;
    $maxEbookMb = rtrim(rtrim(number_format(config('bookplanet.max_ebook_kb') / 1024, 1), '0'), '.');
    $maxImageMb = rtrim(rtrim(number_format(config('bookplanet.max_image_kb') / 1024, 1), '0'), '.');
    $preview = clone $book;
    $preview->title = old('title', $book->title) ?: 'Untitled';
    $previewAuthor = $authors->firstWhere('id', (int) old('author_id', $book->author_id))?->name;
    $authorOptions = $authors->pluck('name', 'id')->all();
    $categoryOptions = $categories->pluck('name', 'id')->all();
@endphp
<x-error-summary action="save this book"/>

<form class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8" method="POST" enctype="multipart/form-data"
      action="{{ $editing ? route('admin.books.update', $book) : route('admin.books.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="grid content-start gap-6 lg:col-span-8">
        <section class="card grid gap-5 p-5 sm:p-6" aria-labelledby="details-h">
            <h2 class="h4" id="details-h">Details</h2>
            <x-input name="title" label="Title" :value="$book->title" required maxlength="255"/>
            <x-input name="slug" label="Slug" optional addon="/books/" :value="$book->slug" maxlength="190" pattern="[A-Za-z0-9_\-]+" autocomplete="off" data-slug-from="title" hint="Used in the web address. Letters, numbers and dashes. Leave empty to make one from the title."/>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="grid content-start gap-1">
                    <x-select name="author_id" label="Author" :options="$authorOptions" :selected="$book->author_id" placeholder="Choose an author" required/>
                    <a class="link justify-self-start text-sm" href="{{ route('admin.authors.create') }}">New author</a>
                </div>
                <x-select name="category_id" label="Category" :options="$categoryOptions" :selected="$book->category_id" placeholder="Choose a category" required/>
            </div>
            <x-textarea name="description" label="Description" :value="$book->description" rows="10" required maxlength="10000" hint="Plain text. Leave a blank line between paragraphs."/>
        </section>

        <section class="card grid gap-5 p-5 sm:p-6" aria-labelledby="price-h">
            <h2 class="h4" id="price-h">Price</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-input name="price" label="Price" :addon="$symbol" inputmode="decimal" :value="Money::decimal($book->price_cents)" required hint="Enter 0 for a free book, or at least {{ $symbol }}0.50." autocomplete="off"/>
                <x-input name="sale_price" label="Sale price" optional :addon="$symbol" inputmode="decimal" :value="Money::decimal($book->sale_price_cents)" hint="Must be lower than the price. Leave empty for no sale." autocomplete="off"/>
            </div>
        </section>

        <section class="card grid gap-5 p-5 sm:p-6" aria-labelledby="bookdetails-h">
            <h2 class="h4" id="bookdetails-h">Book details</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-input name="page_count" label="Pages" optional type="number" min="1" max="100000" inputmode="numeric" :value="$book->page_count"/>
                <x-input name="isbn" label="ISBN" optional :value="$book->isbn" maxlength="20" autocomplete="off"/>
            </div>
            <x-input class="sm:max-w-xs" name="published_at" label="Publication date" optional type="datetime-local" :value="$book->published_at?->format('Y-m-d\TH:i')" hint="Leave empty to publish as soon as the book is visible. A future date schedules it."/>
        </section>
    </div>

    <div class="grid content-start gap-6 lg:col-span-4">
        <section class="card grid gap-2 p-5 sm:p-6" aria-labelledby="visibility-h">
            <h2 class="h4" id="visibility-h">Visibility</h2>
            <x-toggle name="is_published" label="Published" description="Visible in the shop" :checked="$book->is_published"/>
            <x-toggle name="is_featured" label="Featured" description="Shown on the home page" :checked="$book->is_featured"/>
        </section>

        <section class="card grid gap-4 p-5 sm:p-6" aria-labelledby="cover-h">
            <h2 class="h4" id="cover-h">Cover</h2>
            <div class="mx-auto w-40" @unless ($book->cover_path) data-cover-preview data-title-input="title" data-author-input="author-id" @endunless>
                <x-book-cover :book="$preview" :author-name="$previewAuthor ?? ''"/>
            </div>
            @if ($book->cover_path)
                <x-checkbox name="remove_cover" label="Remove the cover image" description="The generated cover is used instead."/>
            @else
                <p class="text-center text-sm text-muted">No image yet: the shop shows this generated cover.</p>
            @endif
            <x-file-input name="cover" :label="$book->cover_path ? 'Replace the image' : 'Cover image'" optional kind="cover" accept="image/jpeg,image/png,image/webp" :constraint="'JPG, PNG or WebP · at least 800 × 1200 px · up to '.$maxImageMb.' MB'"/>
        </section>

        <section class="card grid gap-4 p-5 sm:p-6" aria-labelledby="file-h">
            <h2 class="h4" id="file-h">Ebook file</h2>
            <x-file-input name="ebook" :label="$editing ? 'Replace the file' : 'EPUB or PDF file'" :optional="$editing" kind="file" :required="! $editing"
                accept=".epub,.pdf,application/epub+zip,application/pdf" :constraint="'EPUB or PDF, up to '.$maxEbookMb.' MB'">
                @if ($editing && $book->file_format)
                    <x-slot:current>
                        <div class="card flex items-center gap-3 p-3" data-file-current>
                            <span class="inline-grid size-10 flex-none place-items-center rounded-sm bg-sunken"><x-icon name="file-text"/></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold">{{ $book->downloadFilename() }}</span>
                                <span class="block text-sm text-muted">{{ strtoupper($book->file_format) }} · {{ Number::fileSize((int) $book->file_size, maxPrecision: 1) }}</span>
                            </span>
                            <a class="btn btn-ghost btn-sm" href="{{ route('library.download', $book) }}" aria-label="Download the current file"><x-icon name="download"/></a>
                        </div>
                    </x-slot:current>
                @endif
            </x-file-input>
        </section>
    </div>

    <div class="form-actions lg:col-span-12">
        <x-button variant="ghost" :href="route('admin.books.index')">Cancel</x-button>
        <button class="btn btn-primary" type="submit" data-busy-label="Uploading…">{{ $editing ? 'Save changes' : 'Create book' }}</button>
    </div>
</form>
