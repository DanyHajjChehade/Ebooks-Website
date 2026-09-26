<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use App\Rules\ValidUtf8;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Form fields: name, slug (optional), bio, photo (image), remove_photo (checkbox).
 */
class AuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remove_photo' => $this->boolean('remove_photo'),
            // Normalised here so the unique rule checks the value that will be saved.
            'slug' => is_string($this->input('slug')) && Str::slug($this->input('slug')) !== '' ? Str::slug($this->input('slug')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Author|null $author */
        $author = $this->route('author');

        return [
            'name' => ['required', 'string', new ValidUtf8, 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash:ascii', Rule::unique('authors', 'slug')->ignore($author?->getKey())],
            'bio' => ['nullable', 'string', new ValidUtf8, 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('bookplanet.max_image_kb'), 'dimensions:max_width=6000,max_height=6000'],
            'remove_photo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function authorData(): array
    {
        return $this->safe()->only(['name', 'slug', 'bio']);
    }
}
