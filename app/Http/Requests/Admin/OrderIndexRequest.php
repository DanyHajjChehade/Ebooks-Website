<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Http\Requests\FilterRequest;

class OrderIndexRequest extends FilterRequest
{
    protected function filterDefinitions(): array
    {
        return [
            'q' => null,
            'status' => OrderStatus::values(),
        ];
    }
}
