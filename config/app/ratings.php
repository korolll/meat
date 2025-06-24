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

    Models\Assortment::class => [
        'factory' => Rating\RatingFactoryContract::class,
        'ratings' => [
            'common' => Rating\AssortmentRatingCalculator::class,
        ],
    ],

    Models\User::class => [
        'factory' => Rating\RatingFactoryContract::class,
        'ratings' => [
            'customer' => Rating\CustomerRatingCalculator::class,
            'supplier' => Rating\SupplierRatingCalculator::class,
        ],
    ],

];
