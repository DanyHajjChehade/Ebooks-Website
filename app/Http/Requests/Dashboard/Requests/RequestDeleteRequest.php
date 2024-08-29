<?php

namespace App\Http\Requests\Dashboard\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestDeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'=>'required|exists:reqs,id',
        ];
    }
}
