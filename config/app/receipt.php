<?php

use App\Models;
use App\Services\Management\Rating;

return [

    /*
    |--------------------------------------------------------------------------
    | Система рейтингов
    |--------------------------------------------------------------------------
    |
    | ...
    |
    */

    'discount' => [
        'frontol_cache_ttl' => env('APP_RECEIPT_FRONTOL_DISCOUNT_TTL', 7200),
        'cash_reg_cache_ttl' => env('APP_RECEIPT_CASH_REG_DISCOUNT_TTL', 7200)
    ]
];
