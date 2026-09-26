<?php

namespace App\Support;

use Illuminate\Support\Number;

/**
 * Money is stored as integer cents everywhere; format only at the edge.
 * Blade: @money($cents) or {{ \App\Support\Money::format($cents) }}.
 */
final class Money
{
    public static function currency(): string
    {
        return strtoupper((string) config('services.stripe.currency', 'usd'));
    }

    /**
     * "$12.99" (locale-aware, currency from config unless given).
     */
    public static function format(?int $cents, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?? self::currency());

        return (string) Number::currency(($cents ?? 0) / 100, in: $currency, locale: app()->getLocale());
    }

    /**
     * "12.99" — for form inputs (admin price fields).
     */
    public static function decimal(?int $cents): string
    {
        return $cents === null ? '' : number_format($cents / 100, 2, '.', '');
    }
}
