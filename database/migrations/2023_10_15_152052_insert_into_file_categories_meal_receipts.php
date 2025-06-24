<?php

use App\Models\FileCategory;
use Illuminate\Database\Migrations\Migration;

class InsertIntoFileCategoriesMealReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('file_categories')
            ->insert([
                [
                    'id' => FileCategory::ID_MEAL_RECEIPT_FILE,
                    'name' => 'Изображение/видео рецепта',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::table('file_categories')
            ->whereIn('id', [
                FileCategory::ID_MEAL_RECEIPT_FILE,
            ])
            ->delete();
    }
}
