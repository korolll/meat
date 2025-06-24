<?php

use Illuminate\Database\Migrations\Migration;

class InsertIntoFileCategories104 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('file_categories')->insert([
            [
                'id' => 'labtest-file-customer',
                'name' => 'Файл лабораторного исследования заказчика',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'labtest-file-executor',
                'name' => 'Файл лабораторного исследования исполнителя',
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
        DB::table('file_categories')->whereIn('id', ['labtest-file-customer', 'labtest-file-executor'])->delete();
    }
}
