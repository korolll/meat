<?php

use Illuminate\Database\Migrations\Migration;

class InsertIntoFileCategories98 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('file_categories')->insert([
            'id' => 'user-file',
            'name' => 'Файл пользователя',
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
        DB::table('file_categories')->where('id', '=', 'user-file')->delete();
    }
}
