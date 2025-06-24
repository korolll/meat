<?php

use App\Models\ClientShoppingList;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\Client;

/** @var Factory $factory */
$factory->define(ClientShoppingList::class, function (Faker $faker) {
    return [
        'name' => $faker->text(25),
        'client_uuid' => function () {
            return factory(Client::class)->create()->uuid;
        },
    ];
});
