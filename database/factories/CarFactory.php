<?php

use App\Models\Car;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/** @var Factory $factory */
$factory->define(Car::class, function (Faker $faker) {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('delivery-service')->create()->uuid;
        },
        'brand_name' => $faker->sentence(2),
        'model_name' => $faker->sentence(2),
        'license_plate' => Str::lower($faker->bothify('?###??###')),
        'call_sign' => $faker->numerify('######'),
        'max_weight' => $faker->numberBetween(500, 1500),
        'is_active' => true,
    ];
});

$factory->state(Car::class, 'not-active', [
    'is_active' => false,
]);
