<?php

use App\Models\Assortment;
use App\Models\Product;
use App\Models\ReceiptLine;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(ReceiptLine::class, function (Faker $faker) {
    $assortment = factory(Assortment::class)->state('has-image')->create();

    return [
        'assortment_uuid' => $assortment->uuid,
        'product_uuid' => function () use ($assortment) {
            return factory(Product::class)->create([
                'assortment_uuid' => $assortment->uuid,
            ])->uuid;
        },
        'barcode' => $assortment->barcodes[0]->barcode,
        'quantity' => $faker->numberBetween(1, 25),

        'price_with_discount' => $faker->randomFloat(2, 1000, 2000),
        'discount' => $faker->randomFloat(2, 10, 100),

        'total' => function ($attrs) {
            return $attrs['price_with_discount'] * $attrs['quantity'];
        }
    ];
});
