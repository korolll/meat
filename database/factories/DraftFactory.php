<?php

use App\Models\Draft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Draft::class, function () {
    return [
        'user_uuid' => function () {
            return factory(User::class)->create()->uuid;
        },
        'name' => 'draft_name-123',
        'attributes' => [
            'dummy' => 'value',
        ],
    ];
});
