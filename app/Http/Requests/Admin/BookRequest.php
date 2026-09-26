<?php

namespace App\Http\Requests\Admin;

use App\Models\Book;
use App\Rules\EbookFile;
use App\Rules\StripeChargeableAmount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create/update a book. Form fields: title, slug (optional), description,
 * author_id, category_id, price, sale_price (decimal strings, e.g. "12.99"),
 * page_count, isbn, published_at, is_published, is_featured (checkboxes),
 * cover (image), remove_cover (checkbox), ebook (pdf/epub; required on create).
 */
class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'is_featured' => $this->boolean('is_featured'),
            'remove_cover' => $this->boolean('remove_cover'),
            'slug' => filled($this->input('slug')) ? $this->input('slug') : null,
            'sale_price' => filled($this->input('sale_price')) ? $this->input('sale_price') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Book|null $book */
        $book = $this->route('book');
        $maxImage = (int) config('bookplanet.max_image_kb');
        $maxEbook = (int) config('bookplanet.max_ebook_kb');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash:ascii', Rule::unique('books', 'slug')->ignore($book?->getKey())],
            'description' => ['required', 'string', 'max:10000'],
            'author_id' => ['required', 'integer', Rule::exists('authors', 'id')->whereNull('deleted_at')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999.99', new StripeChargeableAmount],
            'sale_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'lt:price', new StripeChargeableAmount],
            'page_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'isbn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9Xx\- ]+$/'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$maxImage, 'dimensions:max_width=6000,max_height=6000'],
            'remove_cover' => ['boolean'],
            'ebook' => [
                $book ? 'nullable' : 'required',
                'file',
                'extensions:pdf,epub',
                'mimetypes:application/pdf,application/epub+zip,application/zip',
                'max:'.$maxEbook,
                new EbookFile,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'author_id' => 'author',
            'category_id' => 'category',
            'ebook' => 'ebook file',
            'sale_price' => 'sale price',
        ];
    }

    /**
     * Validated input mapped onto Book attributes (money in integer cents).
     * Files are handled separately by the controller.
     *
     * @return array<string, mixed>
     */
    public function bookData(?Book $existing = null): array
    {
        $published = $this->boolean('is_published');
        $publishedAt = $this->date('published_at');

        return [
            'title' => $this->validated('title'),
            'slug' => $this->validated('slug'),
            'description' => $this->validated('description'),
            'author_id' => (int) $this->validated('author_id'),
            'category_id' => (int) $this->validated('category_id'),
            'price_cents' => self::toCents($this->validated('price')),
            'sale_price_cents' => $this->validated('sale_price') === null ? null : self::toCents($this->validated('sale_price')),
            'page_count' => $this->validated('page_count'),
            'isbn' => $this->validated('isbn'),
            'is_published' => $published,
            'is_featured' => $this->boolean('is_featured'),
            'published_at' => $publishedAt ?? ($published ? ($existing?->published_at ?? now()) : null),
        ];
    }

    public static function toCents(string|int|float $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
