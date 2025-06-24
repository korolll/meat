<?php

namespace App\Services\Quantity;

class FloatHelper
{
    const PRECISION = 3;
    const EQUAL_DIFF = 0.001;

    public static function isEqual(float $v1, float $v2): bool
    {
        return abs($v1 - $v2) < static::EQUAL_DIFF;
    }

    public static function round(float $v): float
    {
        return round($v, static::PRECISION);
    }
}
