<?php

use App\Models\File;
use App\Models\FileCategory;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(File::class, function (Faker $faker) {
    return [
        'user_uuid' => function () {
            return factory(User::class)->create()->uuid;
        },
        'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        'original_name' => $faker->word . '.jpg',
        'path' => $faker->word . '.jpg',
        'mime_type' => $faker->mimeType,
        'size' => $faker->numberBetween(1, 1024),
    ];
});
