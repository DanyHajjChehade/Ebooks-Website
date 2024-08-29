<?php

namespace App\Http\Requests\Dashboard\Books;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->usertype == '1';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'category_id' => 'required|numeric|exists:categories,id',
            'author_id'=>'required|numeric|exists:authors,id',
            'book_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'discount_price'=>'nullable|numeric',
            'copies_owned'=>'nullable|numeric',
            'status'=>'nullable|numeric|in:1,2,3',
        ];
    }
}
