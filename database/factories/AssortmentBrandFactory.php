<?php

use App\Models\AssortmentBrand;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(AssortmentBrand::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(2),
    ];
});
