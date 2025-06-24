<?php

use App\Models\Stocktaking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Stocktaking::class, function () {
    return [
        'user_uuid' => function () {
            return factory(User::class)->state('store')->create()->uuid;
        },
    ];
});
