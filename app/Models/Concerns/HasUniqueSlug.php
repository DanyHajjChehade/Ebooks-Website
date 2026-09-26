<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Fills the `slug` column from a source attribute when it is left empty and
 * guarantees uniqueness (including soft-deleted rows, which still hold the
 * unique index).
 */
trait HasUniqueSlug
{
    abstract protected function slugSource(): string;

    public static function bootHasUniqueSlug(): void
    {
        static::saving(function (Model $model) {
            /** @var static $model */
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlug((string) $model->getAttribute($model->slugSource()));
            } elseif ($model->isDirty('slug')) {
                $model->slug = $model->uniqueSlug($model->slug);
            }
        });
    }

    public function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $base = Str::limit($base, 180, '');
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if (method_exists($this, 'bootSoftDeletes')) {
            $query->withTrashed();
        }

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }
}
