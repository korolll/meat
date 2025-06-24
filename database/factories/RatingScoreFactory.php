<?php

use App\Models\RatingScore;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

/** @var Factory $factory */
$factory->define(RatingScore::class, function (Faker $faker) {
    return [
        'rated_reference_type' => 'dummy_reference',
        'rated_reference_id' => Uuid::uuid4()->toString(),
        'rated_by_reference_type' => 'dummy_by_reference_by',
        'rated_by_reference_id' => Uuid::uuid4()->toString(),
        'rated_through_reference_type' => 'dummy_through_reference',
        'rated_through_reference_id' => Uuid::uuid4()->toString(),
        'value' => $faker->numberBetween(0, 100),
        'additional_attributes->comment' => 'hello kitty',
        'additional_attributes->weight' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ];
});
