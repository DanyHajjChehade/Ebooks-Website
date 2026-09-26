<?php

namespace App\Http\Requests;


/**
 * Plain `?q=` search (storefront authors list, admin authors/categories).
 */
class SearchRequest extends FilterRequest
{
    protected function filterDefinitions(): array
    {
        return ['q' => null];
    }
}
