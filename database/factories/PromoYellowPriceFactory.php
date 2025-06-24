<?php

use App\Models\Assortment;
use App\Models\PromoYellowPrice;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(PromoYellowPrice::class, function (Faker $faker) {
    return [
        'assortment_uuid' => fn () => factory(Assortment::class)->create()->uuid,
        'price' => $faker->randomFloat(2, 0, 99999),
        'is_enabled' =>$faker->boolean(),
        'start_at' => $faker->dateTimeBetween('+1 day', '+2 day'),
        'end_at' => $faker->dateTimeBetween('+3 day', '+5 day'),
    ];
});
$factory->state(PromoYellowPrice::class, 'enabled', [
    'is_enabled' => true,
]);
$factory->afterCreatingState(PromoYellowPrice::class, 'has-store', function (PromoYellowPrice $promoYellowPrice) {
    $promoYellowPrice->stores()->attach(factory(User::class)->state('store')->create()->uuid);
});

