<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoDiverseFoodClientDiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromoDiverseFoodClientDiscount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'client_uuid' => function() {
                return factory(Client::class)->create()->uuid;
            },
            'discount_percent' => $this->faker->randomFloat(2, 1, 50),
            'start_at' => now()->startOfMonth(),
            'end_at' => now()->endOfMonth(),
        ];
    }
}
