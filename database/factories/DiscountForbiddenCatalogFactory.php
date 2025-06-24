<?php

namespace Database\Factories;

use App\Models\Catalog;
use App\Models\DiscountForbiddenCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountForbiddenCatalogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountForbiddenCatalog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'catalog_uuid' => function () {
                return factory(Catalog::class)->create()->uuid;
            }
        ];
    }
}
