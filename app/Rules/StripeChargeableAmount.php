<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A price (decimal string in major units, e.g. "4.99") must be free (0) or at
 * least Stripe's minimum charge (0.50), so any non-empty paid cart is chargeable.
 */
class StripeChargeableAmount implements ValidationRule
{
    public const MINIMUM_CENTS = 50;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            return; // "numeric" reports this
        }

        $cents = (int) round(((float) $value) * 100);

        if ($cents !== 0 && $cents < self::MINIMUM_CENTS) {
            $fail('The :attribute must be 0 (free) or at least 0.50.');
        }
    }
}
