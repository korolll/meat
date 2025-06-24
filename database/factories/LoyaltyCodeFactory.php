<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\LoyaltyCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


class LoyaltyCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LoyaltyCode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'client_uuid' => function () {
                return factory(Client::class)->create()->uuid;
            },
            'code' => Str::uuid()->toString(),
            'expires_on' => now()->addHours(24),
        ];
    }
}