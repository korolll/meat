<?php

use App\Models\Car;
use App\Models\Driver;
use App\Models\Transportation;
use App\Models\TransportationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Transportation::class, function () {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('delivery-service')->create()->uuid;
        },
        'date' => now()->addDay(),
        'car_uuid' => function (array $transportation) {
            return factory(Car::class)->create([
                'user_uuid' => $transportation['user_uuid'],
            ])->uuid;
        },
        'driver_uuid' => function (array $transportation) {
            return factory(Driver::class)->create([
                'user_uuid' => $transportation['user_uuid'],
            ])->uuid;
        },
        'transportation_status_id' => TransportationStatus::ID_NEW,
        'started_at' => null,
        'finished_at' => null,
    ];
});

$factory->state(Transportation::class, 'on-the-way', [
    'transportation_status_id' => TransportationStatus::ID_ON_THE_WAY,
    'started_at' => now(),
]);

$factory->state(Transportation::class, 'done', [
    'transportation_status_id' => TransportationStatus::ID_DONE,
    'started_at' => now(),
    'finished_at' => now(),
]);
