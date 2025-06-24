<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientCreditCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ClientCreditCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientCreditCard::class;

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
            'generated_order_uuid' => Uuid::uuid4()->toString(),
            'virtual_order_uuid' => Uuid::uuid4()->toString(),
            'card_mask' => $this->faker->numerify('#### **** **** ####'),
            'binding_id' => Str::random(255),
        ];
    }
}
