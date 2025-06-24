<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\Story;
use App\Models\StoryTab;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryTabFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoryTab::class;

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
            'text_color' => $this->faker->hexColor,
            'text' => $this->faker->text,
            'logo_file_uuid' => function () {
                return factory(File::class)->create([
                    'file_category_id' => FileCategory::ID_STORY_IMAGE,
                ])->uuid;
            },
            'story_id' => Story::factory(),
        ];
    }
}
