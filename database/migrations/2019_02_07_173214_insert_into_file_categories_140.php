<?php

use Illuminate\Database\Migrations\Migration;

class InsertIntoFileCategories140 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('file_categories')->insert([
            'id' => 'assortment-file',
            'name' => 'Файл номенклатуры',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('file_categories')->delete(
            'assortment-file'
        );
    }
}
