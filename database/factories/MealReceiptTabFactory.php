<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\MealReceipt;
use App\Models\MealReceiptTab;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealReceiptTabFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MealReceiptTab::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->title,
            'button_title' => $this->faker->title,
            'duration' => $this->faker->randomNumber(),
            'sequence' => $this->faker->randomNumber(),
            'text_color' => $this->faker->hexColor,
            'text' => $this->faker->text,
            'url' => $this->faker->url,
            'file_uuid' => function () {
                return factory(File::class)->create([
                    'file_category_id' => FileCategory::ID_MEAL_RECEIPT_FILE,
                ])->uuid;
            },
            'meal_receipt_uuid' => MealReceipt::factory(),
        ];
    }
}
