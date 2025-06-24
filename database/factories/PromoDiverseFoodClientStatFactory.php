<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientStat;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoDiverseFoodClientStatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromoDiverseFoodClientStat::class;

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
            'month' => $this->faker->date('Y-m'),
            'purchased_count' => $this->faker->numberBetween(1, 10),
            'rated_count' => $this->faker->numberBetween(1, 10),
        ];
    }
}
