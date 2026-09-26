<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Base for GET list pages with search/filter query strings. Unknown or
 * malformed values are dropped instead of failing validation, so stale or
 * hand-edited URLs still render.
 */
abstract class FilterRequest extends FormRequest
{
    /**
     * Filter key => list of allowed values, or null for free text.
     *
     * @return array<string, list<string>|null>
     */
    abstract protected function filterDefinitions(): array;

    /**
     * @return array<string, string>
     */
    protected function filterDefaults(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach ($this->filterDefinitions() as $key => $allowed) {
            $value = $this->query($key);
            // Non-strings and invalid UTF-8 are dropped like any other bad filter value.
            $value = is_string($value) && mb_check_encoding($value, 'UTF-8') ? trim($value) : null;
            $value = ($value === null || $value === '') ? null : mb_substr($value, 0, 100);

            if ($value !== null && $allowed !== null && ! in_array($value, $allowed, true)) {
                $value = null;
            }

            $clean[$key] = $value;
        }

        $this->merge($clean);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->filterDefinitions() as $key => $allowed) {
            $rules[$key] = ['nullable', 'string', 'max:100'];

            if ($allowed !== null) {
                $rules[$key][] = Rule::in($allowed);
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string|null>
     */
    public function filters(): array
    {
        $defaults = $this->filterDefaults();
        $filters = [];

        foreach (array_keys($this->filterDefinitions()) as $key) {
            $filters[$key] = $this->validated($key) ?? ($defaults[$key] ?? null);
        }

        return $filters;
    }
}
