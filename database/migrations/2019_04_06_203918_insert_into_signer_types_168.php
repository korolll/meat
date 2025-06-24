<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertIntoSignerTypes168 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('signer_types')->insert([
            [
                'id' => 'general_director',
                'name' => 'Генеральный директор',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'confidant',
                'name' => 'Доверенное лицо',
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
        DB::table('signer_types')
            ->whereIn('id', ['general_director', 'confidant'])
            ->delete();
    }
}
