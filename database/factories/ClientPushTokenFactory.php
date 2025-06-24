<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPushToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientPushTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientPushToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::random(128),
            'client_uuid' => factory(Client::class)->create()->uuid
        ];
    }
}
