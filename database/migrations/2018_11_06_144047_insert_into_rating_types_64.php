<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertIntoRatingTypes64 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('rating_types')->insert([
            'id' => 'customer',
            'name' => 'Рейтинг покупателя',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rating_types')->insert([
            'id' => 'supplier',
            'name' => 'Рейтинг поставщика',
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
        DB::table('rating_types')->whereIn('id', ['customer', 'supplier'])->delete();
    }
}
