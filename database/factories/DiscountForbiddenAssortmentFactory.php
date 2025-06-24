<?php

namespace Database\Factories;

use App\Models\Assortment;
use App\Models\DiscountForbiddenAssortment;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountForbiddenAssortmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountForbiddenAssortment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'assortment_uuid' => function () {
                return factory(Assortment::class)->create()->uuid;
            }
        ];
    }
}
