<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Stripe's cancel_url carries the session id (and our order id as a fallback).
 * Both are optional: a bare /checkout/cancel still returns to the cart.
 */
class CheckoutCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
