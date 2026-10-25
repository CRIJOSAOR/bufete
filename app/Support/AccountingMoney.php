<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class AccountingMoney
{
    public static function toMinorUnits(string|int|float $amount, string $field = 'monto'): int
    {
        $amount = trim((string) $amount);

        if (! preg_match('/^-?\d+(?:\.\d{1,2})?$/', $amount)) {
            throw ValidationException::withMessages([
                $field => 'El monto debe ser positivo y tener como máximo dos decimales.',
            ]);
        }

        $negative = str_starts_with($amount, '-');
        $amount = $negative ? substr($amount, 1) : $amount;
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $minorUnits = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return $negative ? -$minorUnits : $minorUnits;
    }

    public static function formatMinorUnits(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $absoluteAmount = abs($amount);

        return $sign.intdiv($absoluteAmount, 100).'.'.str_pad((string) ($absoluteAmount % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function formatAmount(string|int|float $amount): string
    {
        return self::formatMinorUnits(self::toMinorUnits($amount));
    }
}
