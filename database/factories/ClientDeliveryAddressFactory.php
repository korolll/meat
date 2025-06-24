<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientDeliveryAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientDeliveryAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientDeliveryAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'client_uuid' => factory(Client::class)->create()->uuid,
            'title' => $this->faker->city,
            'city' => $this->faker->city,
            'street' => $this->faker->streetName,
            'house' => (string)$this->faker->numberBetween(100, 200),
            'floor' => $this->faker->randomDigit,
            'entrance' => $this->faker->randomDigit,
            'apartment_number' => $this->faker->randomDigit,
            'intercom_code' => $this->faker->numerify('########'),
        ];
    }
}
