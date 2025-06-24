<?php

use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\Product;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Product::class, function (Faker $faker) {
    return [
        'quantum' => $faker->numberBetween(1, 10),
        'min_quantum_in_order' => $faker->numberBetween(1, 10),
        'min_delivery_time' => $faker->numberBetween(1, 240),
        'quantity' => $faker->numberBetween(1, 10),
        'price' => $faker->randomFloat(2, 1, 100),
        'price_recommended' => $faker->randomFloat(2, 0, 100),
        'volume' => $faker->randomFloat(2, 1, 9999999999.99),
        'user_uuid' => function () {
            return factory(User::class)->state('supplier')->create()->uuid;
        },
        'assortment_uuid' => function () {
            return factory(Assortment::class)->create()->uuid;
        },
        'catalog_uuid' => function (array $product) {
            return factory(Catalog::class)->create([
                'user_uuid' => $product['user_uuid'],
            ])->uuid;
        },
        'delivery_weekdays' => $faker->randomElements([0, 1, 2, 3, 4, 5, 6], $faker->numberBetween(3, 7)),
        'is_active' => true,
    ];
});

$factory->afterCreatingState(Product::class, 'has-file', function (Product $product, Faker $faker) {
    $file = factory(File::class)->create([
        'file_category_id' => FileCategory::ID_PRODUCT_FILE,
    ]);

    $product->files()->attach($file->uuid, ['public_name' => $faker->sentence(2)]);
});
