<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FilterRequest;

class BookIndexRequest extends FilterRequest
{
    public const STATUSES = ['published', 'draft', 'scheduled', 'featured'];

    protected function filterDefinitions(): array
    {
        return [
            'q' => null,
            'status' => self::STATUSES,
        ];
    }
}
