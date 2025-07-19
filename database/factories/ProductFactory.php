<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(100, 2000),
            'category' => $this->faker->randomElement(['Мясо', 'Колбасы', 'Деликатесы']),
            'in_stock' => $this->faker->boolean(80),
        ];
    }
}
