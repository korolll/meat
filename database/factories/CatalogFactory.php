<?php

use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\FileCategory;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\File;

/** @var Factory $factory */
$factory->define(Catalog::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(3),
    ];
});

$factory->state(Catalog::class, 'private', [
    'user_uuid' => function () {
        return factory(User::class)->state('distribution-center')->create()->uuid;
    },
]);

$factory->state(Catalog::class, 'has-parent', [
    'catalog_uuid' => function () {
        return factory(Catalog::class)->create()->uuid;
    },
]);

$factory->afterCreatingState(Catalog::class, 'has-assortment', function (Catalog $catalog) {
    factory(Assortment::class)->create([
        'catalog_uuid' => $catalog->uuid,
    ]);
});

$factory->afterCreatingState(Catalog::class, 'has-children', function (Catalog $catalog) {
    factory(Catalog::class)->create([
        'catalog_uuid' => $catalog->uuid,
    ]);
});

$factory->afterCreatingState(Catalog::class, 'has-image', function (Catalog $catalog) {
    $params = [
        'file_category_id' => FileCategory::ID_CATALOG_IMAGE
    ];
    if ($catalog->user_uuid) {
        $params['user_uuid'] = $catalog->user_uuid;
    }

    $file = factory(File::class)->create($params);
    $catalog->image()->associate($file)->save();
});
