<?php

namespace Database\Factories;

use App\Models\NotificationTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationTaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NotificationTask::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title_template' => $this->faker->title,
            'body_template' => $this->faker->text,
            'options' => [],
            'execute_at' => $this->faker->dateTimeBetween('+1 day', '+1 week')
        ];
    }
}
