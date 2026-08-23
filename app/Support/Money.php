<?php

namespace App\Support;

use NumberFormatter;

final class Money
{
    public static function format(float|int|string|null $amount, string $currency): string
    {
        $value = (float) ($amount ?? 0);
        $locale = app()->getLocale() === 'en' ? 'en_US' : 'cs_CZ';
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $fractionDigits = fmod($value, 1.0) === 0.0 ? 0 : 2;

        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $fractionDigits);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        return $formatter->formatCurrency($value, strtoupper($currency));
    }
}
