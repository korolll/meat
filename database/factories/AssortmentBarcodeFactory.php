<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Assortment;
use App\Models\AssortmentBarcode;
use Faker\Generator as Faker;

$factory->define(AssortmentBarcode::class, function (Faker $faker) {
    return [
        'assortment_uuid' => function () {
            return factory(Assortment::class)->create()->uuid;
        },
        'barcode' => $faker->ean13,
        'is_active' => true,
        'started_at' => now()
    ];
});

$factory->state(AssortmentBarcode::class, 'ean8', function (Faker $faker) {
    return [
        'barcode' => $faker->ean8,
    ];
});
