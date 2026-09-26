{{-- PLACEHOLDER partial: $book, $authors, $categories. multipart/form-data --}}
@if ($errors->any())<ul role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<label>Title <input name="title" value="{{ old('title', $book->title) }}" required maxlength="255"></label>
<label>Slug (optional) <input name="slug" value="{{ old('slug', $book->slug) }}" maxlength="190"></label>
<label>Description <textarea name="description" required>{{ old('description', $book->description) }}</textarea></label>
<label>Author <select name="author_id" required><option value="">—</option>
    @foreach ($authors as $author)<option value="{{ $author->id }}" @selected((string) old('author_id', $book->author_id) === (string) $author->id)>{{ $author->name }}</option>@endforeach
</select></label>
<label>Category <select name="category_id" required><option value="">—</option>
    @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $book->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach
</select></label>
<label>Price ({{ \App\Support\Money::currency() }}, 0 = free, otherwise ≥ 0.50) <input name="price" inputmode="decimal" value="{{ old('price', \App\Support\Money::decimal($book->price_cents)) }}" required></label>
<label>Sale price (optional) <input name="sale_price" inputmode="decimal" value="{{ old('sale_price', \App\Support\Money::decimal($book->sale_price_cents)) }}"></label>
<label>Pages <input type="number" name="page_count" min="1" value="{{ old('page_count', $book->page_count) }}"></label>
<label>ISBN <input name="isbn" value="{{ old('isbn', $book->isbn) }}" maxlength="20"></label>
<label>Publish date <input type="datetime-local" name="published_at" value="{{ old('published_at', $book->published_at?->format('Y-m-d\TH:i')) }}"></label>
<label><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $book->is_published))> Published</label>
<label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $book->is_featured))> Featured</label>
<label>Cover image (JPG/PNG/WebP, max {{ config('bookplanet.max_image_kb') }} KB) <input type="file" name="cover" accept="image/jpeg,image/png,image/webp"></label>
@if ($book->cover_url)<img src="{{ $book->cover_url }}" alt="" width="80"> <label><input type="checkbox" name="remove_cover" value="1"> Remove cover</label>@endif
<label>Ebook file (PDF/EPUB, max {{ config('bookplanet.max_ebook_kb') }} KB){{ $book->exists ? ' — leave empty to keep '.strtoupper($book->file_format) : '' }}
    <input type="file" name="ebook" accept=".pdf,.epub,application/pdf,application/epub+zip" @required(! $book->exists)></label>
