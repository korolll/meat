<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('category');
            $table->boolean('in_stock')->default(true);
            $table->timestamps();
        });

        // Добавляем тестовые данные
        DB::table('products')->insert([
            [
                'name' => 'Говядина',
                'description' => 'Говядина',
                'price' => 500.00,
                'category' => 'Мясо',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Свинина',
                'description' => 'Свинина',
                'price' => 400.00,
                'category' => 'Мясо',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Колбаса',
                'description' => 'Колбаса',
                'price' => 600.00,
                'category' => 'Колбасы',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Сосиски',
                'description' => 'Сосиски',
                'price' => 350.00,
                'category' => 'Колбасы',
                'in_stock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
