<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FilterRequest;

/**
 * Plain `?q=` search used by the authors and categories lists.
 */
class SearchRequest extends FilterRequest
{
    protected function filterDefinitions(): array
    {
        return ['q' => null];
    }
}
