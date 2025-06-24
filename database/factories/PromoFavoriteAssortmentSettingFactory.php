<?php

namespace Database\Factories;

use App\Models\PromoFavoriteAssortmentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoFavoriteAssortmentSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromoFavoriteAssortmentSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'threshold_amount' => $this->faker->randomFloat(2, 1, 999999),
            'number_of_sum_days' => $this->faker->numberBetween(1, 30),
            'number_of_active_days' => $this->faker->numberBetween(1, 30),
            'discount_percent' => $this->faker->randomFloat(2, 10, 30),
            'is_enabled' => true,
        ];
    }
}
