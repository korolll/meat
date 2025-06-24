<?php

use App\Models\File;
use App\Models\FileCategory;
use App\Models\LoyaltyCardType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(LoyaltyCardType::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(2),
        'logo_file_uuid' => function () {
            return factory(File::class)->create([
                'file_category_id' => FileCategory::ID_LOYALTY_CARD_TYPE_LOGO,
            ])->uuid;
        },
    ];
});
