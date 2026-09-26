<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutSuccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * `session_id` comes from Stripe's redirect; `order` is used for free orders.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['nullable', 'string', 'max:255', 'required_without:order'],
            'order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
