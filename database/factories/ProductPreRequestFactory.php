<?php

use App\Models\ProductPreRequest;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(ProductPreRequest::class, function (Faker $faker) {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('distribution-center')->create()->uuid;
        },
        'delivery_date' => $faker->dateTimeBetween('+2 day', '+7 day'),
        'confirmed_delivery_date' => $faker->dateTimeBetween('+2 day', '+7 day'),
        'status' => ProductPreRequest::STATUS_NEW,
    ];
});
