<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
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
            'slug' => filled($this->input('slug')) ? $this->input('slug') : null,
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash:ascii', Rule::unique('categories', 'slug')->ignore($category?->getKey())],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
