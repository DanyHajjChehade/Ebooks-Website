<?php

namespace App\Http\Requests;

use App\Models\Book;
use App\Models\Review;
use App\Rules\ValidUtf8;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ReviewRequest extends FormRequest
{
    /**
     * ReviewPolicy runs before validation, so non-owners get a 403 rather than
     * validation errors. (Controllers authorise again explicitly.)
     */
    public function authorize(): Response
    {
        $review = $this->route('review');
        $book = $this->route('book');

        return match (true) {
            $review instanceof Review => Gate::inspect('update', $review),
            $book instanceof Book => Gate::inspect('create', [Review::class, $book]),
            default => Response::deny(),
        };
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body'))) {
            $this->merge(['body' => trim($this->input('body'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', new ValidUtf8, 'min:2', 'max:'.Review::MAX_BODY],
        ];
    }
}
