<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects byte sequences that are not valid UTF-8. Without this, SQLite
 * stores them as-is and `/u` regexes in views later fail on them.
 */
class ValidUtf8 implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
            $fail('The :attribute contains characters we can’t read. Please retype it.');
        }
    }
}
