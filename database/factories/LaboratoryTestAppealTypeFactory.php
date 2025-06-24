<?php

use App\Models\LaboratoryTestAppealType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(LaboratoryTestAppealType::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(2),
    ];
});
