<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertIntoFileCategories51 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('file_categories')->insert([
            'id' => 'loyalty-card-type-logo',
            'name' => 'Логотип типа карты лояльности',
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
            'loyalty-card-type-logo'
        );
    }
}
