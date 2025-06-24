<?php

use App\Models\AssortmentProperty;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(AssortmentProperty::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(3),
        'assortment_property_data_type_id' => \App\Models\AssortmentPropertyDataType::ID_STRING,
        'available_values' => null,
        'is_searchable' => false
    ];
});

$factory->state(AssortmentProperty::class, 'searchable', [
    'is_searchable' => true,
]);
