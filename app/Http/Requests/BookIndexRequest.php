<?php

namespace App\Http\Requests;

/**
 * Storefront catalogue filters: q, category (slug), author (slug), sort.
 */
class BookIndexRequest extends FilterRequest
{
    public const SORTS = ['newest', 'price_asc', 'price_desc', 'title'];

    protected function filterDefinitions(): array
    {
        return [
            'q' => null,
            'category' => null,
            'author' => null,
            'sort' => self::SORTS,
        ];
    }

    protected function filterDefaults(): array
    {
        return ['sort' => 'newest'];
    }
}
