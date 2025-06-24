<?php

namespace Database\Factories;

use App\Models\Assortment;
use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodClientStatAssortment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoDiverseFoodClientStatAssortmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromoDiverseFoodClientStatAssortment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'promo_diverse_food_client_stat_uuid' => PromoDiverseFoodClientStat::factory(),
            'assortment_uuid' => function() {
                return factory(Assortment::class)->create();
            },
            'is_rated' => $this->faker->boolean
        ];
    }
}
