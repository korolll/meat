<?php

use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(PriceList::class, function (Faker $faker) {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('distribution-center')->create()->uuid;
        },
        'name' => $faker->sentence(3),
        'customer_user_uuid' => null,
        'price_list_status_id' => PriceListStatus::FUTURE,
        'date_from' => $faker->dateTimeBetween('+1 day', '+1 month'),
        'date_till' => null,
    ];
});

$factory->state(PriceList::class, 'private', [
    'customer_user_uuid' => function () {
        return factory(User::class)->state('store')->create()->uuid;
    },
]);
