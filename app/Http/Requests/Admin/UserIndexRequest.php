<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FilterRequest;

class UserIndexRequest extends FilterRequest
{
    protected function filterDefinitions(): array
    {
        return [
            'q' => null,
            'role' => ['admin', 'customer'],
        ];
    }
}
