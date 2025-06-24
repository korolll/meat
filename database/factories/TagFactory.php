<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(\App\Models\Tag::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(3),
        'fixed_in_filters' => $faker->boolean(),
    ];
});