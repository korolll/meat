<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Story::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'logo_file_uuid' => function () {
                return factory(File::class)->create([
                    'file_category_id' => FileCategory::ID_STORY_IMAGE,
                ])->uuid;
            },
            'show_from' => $this->faker->dateTime(),
            'show_to' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ];
    }
}
