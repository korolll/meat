<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(\App\Models\Region::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(3)
    ];
});
