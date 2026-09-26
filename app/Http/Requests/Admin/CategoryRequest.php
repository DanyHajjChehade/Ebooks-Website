<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Rules\ValidUtf8;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Form fields: name, slug (optional), description.
 */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Normalised here so the unique rule checks the value that will be saved.
            'slug' => is_string($this->input('slug')) && Str::slug($this->input('slug')) !== '' ? Str::slug($this->input('slug')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', new ValidUtf8, 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash:ascii', Rule::unique('categories', 'slug')->ignore($category?->getKey())],
            'description' => ['nullable', 'string', new ValidUtf8, 'max:2000'],
        ];
    }
}
