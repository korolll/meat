<?php

use App\Models\Client;
use App\Models\User;
use App\Models\UserType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Client::class, function (Faker $faker) {
    return [
        'phone' => $faker->numerify('+79#########'),
        'name' => $faker->name,
        'email' => $faker->email,
        'birth_date' => $faker->dateTime(),
        'sex' => $faker->randomElement(['male', 'female']),
        'consent_to_service_newsletter' => $faker->boolean,
        'consent_to_receive_promotional_mailings' => $faker->boolean,
        'is_agree_with_diverse_food_promo' => $faker->boolean,
        'selected_store_user_uuid' => null,
        'bonus_balance' => $faker->numberBetween(100, 1000)
    ];
});

$factory->state(Client::class, 'with-selected-store', function () {
    return [
        'selected_store_user_uuid' => factory(User::class)->create([
            'user_type_id' => UserType::ID_STORE
        ])->uuid,
    ];
});
