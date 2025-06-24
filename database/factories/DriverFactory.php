<?php

use App\Models\Driver;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/** @var Factory $factory */
$factory->define(Driver::class, function (Faker $faker) {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('delivery-service')->create()->uuid;
        },
        'full_name' => $faker->name,
        'email' => $faker->unique()->email,
        'password' => $faker->password(8, 50),
        'hired_on' => $faker->iso8601,
        'fired_on' => null,
        'comment' => $faker->sentence(3),
        'license_number' => Str::upper($faker->bothify('##??######')),
    ];
});

$factory->state(Driver::class, 'fired', function (Faker $faker) {
    return [
        'fired_on' => $faker->iso8601,
    ];
});

$factory->state(Driver::class, 'random-fired', function (Faker $faker) {
    return [
        'fired_on' => $faker->boolean(75) ? null : $faker->iso8601,
    ];
});
