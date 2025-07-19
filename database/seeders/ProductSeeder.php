<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Product::insert([
            [
                'name' => 'Говядина',
                'description' => 'Говядина',
                'price' => 1200,
                'category' => 'Мясо',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Свинина',
                'description' => 'Свинина',
                'price' => 900,
                'category' => 'Мясо',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Колбаса',
                'description' => 'Колбаса',
                'price' => 600,
                'category' => 'Колбасы',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Сосиски',
                'description' => 'Сосиски',
                'price' => 350,
                'category' => 'Колбасы',
                'in_stock' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
