<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $price_cents
 * @property int|null $sale_price_cents
 * @property-read int $effective_price_cents
 */
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, HasUniqueSlug, SoftDeletes;

    public const FORMATS = ['pdf', 'epub'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'author_id',
        'category_id',
        'price_cents',
        'sale_price_cents',
        'cover_path',
        'file_path',
        'file_format',
        'file_size',
        'page_count',
        'isbn',
        'is_published',
        'is_featured',
        'published_at',
    ];

    /**
     * The private file location is never serialised.
     *
     * @var list<string>
     */
    protected $hidden = [
        'file_path',
    ];

    protected $attributes = [
        'is_published' => false,
        'is_featured' => false,
        'file_size' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'sale_price_cents' => 'integer',
            'file_size' => 'integer',
            'page_count' => 'integer',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    /**
     * Visible in the storefront: published and not scheduled for the future.
     *
     * @param  Builder<Book>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_published'), true)
            ->where(fn (Builder $q) => $q
                ->whereNull($this->qualifyColumn('published_at'))
                ->orWhere($this->qualifyColumn('published_at'), '<=', now()));
    }

    /**
     * Storefront search / filter / sort. Expects the keys produced by
     * BookIndexRequest::filters(): q, category, author, sort.
     *
     * @param  Builder<Book>  $query
     * @param  array{q?: ?string, category?: ?string, author?: ?string, sort?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                // Strip LIKE wildcards: there is no portable escape character across SQLite/MySQL/Postgres.
                $like = '%'.str_replace(['%', '_'], ' ', $term).'%';

                $query->where(fn (Builder $q) => $q
                    ->whereLike('books.title', $like)
                    ->orWhereLike('books.description', $like)
                    ->orWhere('books.isbn', $term)
                    ->orWhereHas('author', fn (Builder $a) => $a->whereLike('name', $like)));
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query
                ->whereHas('category', fn (Builder $c) => $c->where('slug', $slug)))
            ->when($filters['author'] ?? null, fn (Builder $query, string $slug) => $query
                ->whereHas('author', fn (Builder $a) => $a->where('slug', $slug)));

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw(static::effectivePriceSql().' asc')->orderBy('books.title'),
            'price_desc' => $query->orderByRaw(static::effectivePriceSql().' desc')->orderBy('books.title'),
            'title' => $query->orderBy('books.title'),
            default => $query->orderByDesc('books.published_at')->orderByDesc('books.id'),
        };
    }

    /**
     * Portable SQL expression for the price a customer actually pays.
     */
    public static function effectivePriceSql(): string
    {
        return 'COALESCE(books.sale_price_cents, books.price_cents)';
    }

    /**
     * @return Attribute<int, never>
     */
    protected function effectivePriceCents(): Attribute
    {
        return Attribute::get(fn (): int => $this->isOnSale()
            ? (int) $this->sale_price_cents
            : (int) $this->price_cents);
    }

    public function isOnSale(): bool
    {
        return $this->sale_price_cents !== null && $this->sale_price_cents < $this->price_cents;
    }

    public function isFree(): bool
    {
        return $this->effective_price_cents === 0;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published
            && ! $this->trashed()
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    /**
     * Public URL of the cover image, or null (the frontend renders a generated cover).
     *
     * @return Attribute<?string, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_path
            ? Storage::disk(config('bookplanet.media_disk'))->url($this->cover_path)
            : null);
    }

    /**
     * Suggested file name for downloads, e.g. "the-salt-cartographer.pdf".
     */
    public function downloadFilename(): string
    {
        return ($this->slug ?: 'ebook').'.'.$this->file_format;
    }

    /**
     * Author and category resolve even when soft-deleted so order history and
     * libraries never break.
     *
     * @return BelongsTo<Author, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Users who have this book in their library.
     *
     * @return BelongsToMany<User, $this>
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('order_id')
            ->withTimestamps();
    }
}
