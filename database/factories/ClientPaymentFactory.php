<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ClientPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientPayment::class;

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
            'generated_order_uuid' => Uuid::uuid4()->toString(),
            'amount' => $this->faker->randomDigit,
            'binding_id' => Str::random(255),
            'related_reference_type' => Str::random(),
            'related_reference_id' => Uuid::uuid4()->toString(),
        ];
    }
}
