<?php

use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyCardType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(LoyaltyCard::class, function (Faker $faker) {
    return [
        'loyalty_card_type_uuid' => function () {
            return factory(LoyaltyCardType::class)->create()->uuid;
        },
        'number' => $faker->ean13,
    ];
});

$factory->state(LoyaltyCard::class, 'owned', [
    'client_uuid' => function () {
        return factory(Client::class)->create()->uuid;
    },
]);
