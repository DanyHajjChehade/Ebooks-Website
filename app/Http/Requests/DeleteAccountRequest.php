<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    /**
     * Errors go to the `userDeletion` bag (profile page shows them in the delete dialog).
     */
    protected $errorBag = 'userDeletion';

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
            'password' => ['required', 'current_password'],
        ];
    }
}
