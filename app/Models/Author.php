<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory, HasUniqueSlug, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'photo_path',
    ];

    protected function slugSource(): string
    {
        return 'name';
    }

    /**
     * @return HasMany<Book, $this>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Public URL of the author photo, or null (the frontend renders a fallback).
     *
     * @return Attribute<?string, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo_path
            ? Storage::disk(config('bookplanet.media_disk'))->url($this->photo_path)
            : null);
    }
}
