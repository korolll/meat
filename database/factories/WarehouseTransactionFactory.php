<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(\App\Models\WarehouseTransaction::class, function (Faker $faker) {
    return [
        'product_uuid' => function () {
            return factory(\App\Models\Product::class)->create()->uuid;
        },
        'quantity_old' => $faker->numberBetween(),
        'quantity_new' => $faker->numberBetween(),
        'quantity_delta' => function ($entity) {
            return $entity['quantity_new'] - $entity['quantity_old'];
        },
        'reference_type' => 'dummy',
        'reference_id' => null
    ];
});
