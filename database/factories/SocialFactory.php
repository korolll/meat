<?php

use App\Models\File;
use App\Models\FileCategory;
use App\Models\Social;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Social::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(2),
        'sort_nuber' => $faker->sentence(),
        'url' => $faker->sentence(2),
        'logo_file_uuid' => function () {
            return factory(File::class)->create([
                'file_category_id' => FileCategory::ID_SOCIAL_LOGO,
            ])->uuid;
        },
    ];
});
