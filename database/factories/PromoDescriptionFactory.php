<?php

use App\Models\File;
use App\Models\FileCategory;
use App\Models\PromoDescription;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(PromoDescription::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(2),
        'title' => $faker->sentence(100),
        'description' => $faker->sentence(500),
        'logo_file_uuid' => function () {
            return factory(File::class)->create([
                'file_category_id' => FileCategory::ID_PROMO_LOGO,
            ])->uuid;
        },

        'discount_type' => null,
        'color' => $faker->hexColor,
        'is_hidden' => $faker->boolean,
    ];
});
