<?php

use App\Models\ProductRequest;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\ProductRequestDeliveryStatus;
use App\Models\Transportation;
use App\Models\TransportationPoint;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(ProductRequest::class, function (Faker $faker) {
    return [
        'customer_user_uuid' => function () {
            return factory(User::class)->state('store')->create()->uuid;
        },
        'supplier_user_uuid' => function () {
            return factory(User::class)->state('distribution-center')->create()->uuid;
        },
        'delivery_user_uuid' => function () {
            return factory(User::class)->state('delivery-service')->create()->uuid;
        },
        'expected_delivery_date' => $faker->dateTimeBetween('+1 day', '+7 day'),
        'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
    ];
});

$factory->state(ProductRequest::class, 'waiting-for-delivery', function (Faker $faker) {
    return [
        'delivery_user_uuid' => null,
        'product_request_delivery_status_id' => ProductRequestDeliveryStatus::ID_WAITING,
        'confirmed_date' => $faker->dateTimeBetween('0 day', '+30 day'),
    ];
});

$factory->state(ProductRequest::class, 'has-transportation', function (Faker $faker) {
    return [
        'product_request_delivery_status_id' => ProductRequestDeliveryStatus::ID_IN_WORK,
        'transportation_uuid' => function (array $productRequest) {
            return factory(Transportation::class)->state('on-the-way')->create([
                'user_uuid' => $productRequest['delivery_user_uuid'],
            ])->uuid;
        },
        'confirmed_date' => $faker->dateTimeBetween('0 day', '+30 day'),
    ];
});

$factory->state(ProductRequest::class, 'self-delivery', [
    'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_SELF_DELIVERY,
]);

$factory->afterCreatingState(ProductRequest::class, 'has-transportation', function ($productRequest) {
    foreach (['loading', 'unloading'] as $state) {
        factory(TransportationPoint::class)->state($state)->create([
            'transportation_uuid' => $productRequest->transportation_uuid,
            'product_request_uuid' => $productRequest->uuid,
        ]);
    }
});
