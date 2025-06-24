<?php

namespace Database\Factories;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientActivePromoFavoriteAssortment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientActivePromoFavoriteAssortmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientActivePromoFavoriteAssortment::class;

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
            'assortment_uuid' => function () {
                return factory(Assortment::class)->create()->uuid;
            },
            'active_from' => $this->faker->dateTimeBetween('+1 day', '+1 day 10 hours'),
            'active_to' => $this->faker->dateTimeBetween('+5 days', '+14 days'),
            'discount_percent' => $this->faker->randomFloat(2, 10, 30),
        ];
    }
}
