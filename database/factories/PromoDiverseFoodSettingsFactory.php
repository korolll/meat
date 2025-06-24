
<?php

use App\Models\PromoDiverseFoodSettings;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(PromoDiverseFoodSettings::class, function (Faker $faker) {
    return [
        'count_purchases' => $faker->numberBetween(2, 10),
        'count_rating_scores' => $faker->numberBetween(1, 10),
        'discount_percent' => $faker->randomFloat(2, 0, 100),
        'is_enabled' =>$faker->boolean(),
    ];
});
$factory->state(PromoDiverseFoodSettings::class, 'enabled', [
    'is_enabled' => true,
]);
