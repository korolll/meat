<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\MealReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealReceiptFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MealReceipt::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'section' => $this->faker->word,
            'title' => $this->faker->title,
            'description' => $this->faker->text(100),
            'ingredients' => [[
                'name' => $this->faker->word,
                'quantity' => (string)$this->faker->randomDigit
            ]],
            'file_uuid' => function () {
                return factory(File::class)->create([
                    'file_category_id' => FileCategory::ID_MEAL_RECEIPT_FILE,
                ])->uuid;
            },
        ];
    }
}
