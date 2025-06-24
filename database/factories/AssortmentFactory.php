<?php

use App\Models\Assortment;
use App\Models\AssortmentBarcode;
use App\Models\AssortmentBrand;
use App\Models\AssortmentUnit;
use App\Models\AssortmentVerifyStatus;
use App\Models\Catalog;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\ProductionStandard;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Assortment::class, function (Faker $faker) {
    return [
        'catalog_uuid' => function () {
            return factory(Catalog::class)->create()->uuid;
        },
        'name' => $faker->sentence(3),
        'short_name' => $faker->sentence(1),
        'assortment_unit_id' => AssortmentUnit::ID_PIECE,
        'country_id' => $faker->countryCode,
        'okpo_code' => $faker->numerify('#####'),
        'weight' => $faker->randomFloat(2, 1, 100),
        'volume' => $faker->randomFloat(2, 1, 99999999999),
        'manufacturer' => $faker->sentence(3),
        'ingredients' => $faker->sentence(3),
        'description' => $faker->sentence(3),
        'group_barcode' => $faker->numerify('(##)##############(##)######(##)######(##)#####(##)#####'),
        'temperature_min' => $faker->numberBetween(-100, 0),
        'temperature_max' => $faker->numberBetween(0, 100),
        'production_standard_id' => ProductionStandard::ID_GOST,
        'production_standard_number' => $faker->numerify('##########'),
        'is_storable' => $faker->boolean,
        'shelf_life' => $faker->numberBetween(1, 99999),
        'nds_percent' => function () use ($faker) {
            return $faker->randomElement(config('app.nds-percents'));
        },
        'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        'assortment_brand_uuid' => function () {
            return factory(AssortmentBrand::class)->create()->uuid;
        },
        'declaration_end_date' => $faker->dateTimeBetween('-5 years', '+5 years'),
        'article' => $faker->uuid,
    ];
});

$factory->afterCreatingState(Assortment::class, 'has-image', function (Assortment $assortment) {
    $file = factory(File::class)->create([
        'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
    ]);

    $assortment->images()->attach([$file->uuid => ['file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE]]);
});
$factory->afterCreating(Assortment::class, function (Assortment $assortment) {
    $assortmentBarcode = factory(AssortmentBarcode::class)->create([
        'assortment_uuid' => $assortment->uuid,
    ]);
    $assortment->barcodes()->save($assortmentBarcode);
    $assortment->load('barcodes');
});
