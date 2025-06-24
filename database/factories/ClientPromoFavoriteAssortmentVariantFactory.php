<?php

namespace Database\Factories;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientPromoFavoriteAssortmentVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientPromoFavoriteAssortmentVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientPromoFavoriteAssortmentVariant::class;

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
            'can_be_activated_till' => $this->faker->dateTimeBetween('+1 day', '+1 week')
        ];
    }
}
