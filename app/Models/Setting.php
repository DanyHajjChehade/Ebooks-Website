<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Single-row site settings. Read through Setting::current(), which is cached
 * and busted whenever the row is saved or deleted.
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    public const CACHE_KEY = 'bookplanet.settings';

    public const SOCIAL_FIELDS = ['facebook_url', 'instagram_url', 'x_url', 'youtube_url', 'tiktok_url'];

    protected $fillable = [
        'site_name',
        'tagline',
        'contact_email',
        'phone',
        'address',
        'facebook_url',
        'instagram_url',
        'x_url',
        'youtube_url',
        'tiktok_url',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * The settings row (or unsaved defaults when none exists yet).
     */
    public static function current(): self
    {
        $attributes = Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->oldest('id')->first()?->getAttributes();
        });

        if ($attributes === null) {
            return new static(static::defaults());
        }

        return (new static)->newFromBuilder($attributes);
    }

    /**
     * @return array<string, string|null>
     */
    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Book Planet'),
            'tagline' => 'Independent ebooks, beautifully made.',
            'contact_email' => null,
        ];
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Social links that are filled in, keyed by network (facebook, instagram, x, youtube, tiktok).
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $links = [];

        foreach (self::SOCIAL_FIELDS as $field) {
            if (filled($this->{$field})) {
                $links[str_replace('_url', '', $field)] = $this->{$field};
            }
        }

        return $links;
    }
}
