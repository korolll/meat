<?php

use Illuminate\Database\Migrations\Migration;

class InsertIntoAssortmentPropertyDataTypes177 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('assortment_property_data_types')->insert([
            [
                'id' => 'string',
                'name' => 'Строка',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'number',
                'name' => 'Число',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'enum',
                'name' => 'Перечисление',
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
        DB::table('assortment_property_data_types')
            ->whereIn('id', ['string', 'number', 'enum'])
            ->delete();
    }
}
