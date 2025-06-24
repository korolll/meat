<?php

use App\Models\ProductRequest;
use App\Models\Transportation;
use App\Models\TransportationPoint;
use App\Models\TransportationPointType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(TransportationPoint::class, function (Faker $faker) {
    return [
        'transportation_uuid' => function () {
            return factory(Transportation::class)->create()->uuid;
        },
        'product_request_uuid' => function (array $transportationPoint) {
            return factory(ProductRequest::class)->create([
                'transportation_uuid' => $transportationPoint['transportation_uuid'],
            ])->uuid;
        },
        'transportation_point_type_id' => TransportationPointType::ID_LOADING,
        'address' => $faker->address,
        'arrived_at' => null,
        'order' => 0,
    ];
});

$factory->state(TransportationPoint::class, 'loading', [
    'transportation_point_type_id' => TransportationPointType::ID_LOADING,
    'order' => 0,
]);

$factory->state(TransportationPoint::class, 'unloading', [
    'transportation_point_type_id' => TransportationPointType::ID_UNLOADING,
    'order' => 1,
]);
