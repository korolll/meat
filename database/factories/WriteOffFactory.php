<?php

use App\Models\Product;
use App\Models\User;
use App\Models\WriteOff;
use App\Models\WriteOffReason;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(WriteOff::class, function (Faker $faker) {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('store')->create()->uuid;
        },
        'product_uuid' => function (array $writeOff) {
            return factory(Product::class)->create([
                'user_uuid' => $writeOff['user_uuid'],
            ])->uuid;
        },
        'write_off_reason_id' => WriteOffReason::ID_SHELF_LIFE,
        'quantity_delta' => $faker->numberBetween(-10, -1),
        'comment' => $faker->sentence(2),
    ];
});
