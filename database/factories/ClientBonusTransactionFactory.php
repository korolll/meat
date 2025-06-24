<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientBonusTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ClientBonusTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientBonusTransaction::class;

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

            'quantity_old' => $this->faker->numberBetween(1, 1000),
            'quantity_new' => $this->faker->numberBetween(1, 1000),
            'quantity_delta' => function ($entity) {
                return $entity['quantity_new'] - $entity['quantity_old'];
            },

            'related_reference_type' => Str::random(),
            'related_reference_id' => Uuid::uuid4()->toString(),
        ];
    }
}
