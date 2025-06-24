<?php

use Illuminate\Database\Migrations\Migration;

class InsertIntoFileCategories185 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('file_categories')->insert([
            'id' => 'social-logo',
            'name' => 'Лого для соц. сети',
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
        DB::table('file_categories')->where('id', '=', 'social-logo')->delete();
    }
}
