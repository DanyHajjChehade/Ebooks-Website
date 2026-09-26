<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FilterRequest;

class ReviewIndexRequest extends FilterRequest
{
    protected function filterDefinitions(): array
    {
        return [
            'q' => null,
            'rating' => ['1', '2', '3', '4', '5'],
        ];
    }
}
