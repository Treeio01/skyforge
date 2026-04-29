<?php

declare(strict_types=1);

namespace App\Support\Admin;

final class MoneyFormatter
{
    public static function format(int $kopecks): string
    {
        return number_format($kopecks / 100, 2, '.', ' ').' ₽';
    }

    public static function csv(int $kopecks): string
    {
        return number_format($kopecks / 100, 2, '.', '');
    }

    /** Returns a closure for ->modifyRawValue() on money fields. */
    public static function field(): \Closure
    {
        return fn (mixed $value) => self::format((int) $value);
    }
}
