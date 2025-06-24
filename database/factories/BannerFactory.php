<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\File;
use App\Models\FileCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    private const REFERENCE_TYPES = ['catalog', 'product'];
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'logo_file_uuid' => function () {
                return factory(File::class)->create([
                    'file_category_id' => FileCategory::ID_BANNER_IMAGE,
                ])->uuid;
            },
            'number' => $this->faker->randomNumber(),
            'enabled' => true,
            'reference_type' => self::REFERENCE_TYPES[array_rand(self::REFERENCE_TYPES)],
            'reference_uuid' => $this->faker->uuid
        ];
    }
}
