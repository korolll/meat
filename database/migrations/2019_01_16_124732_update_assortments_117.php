<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAssortments117 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('assortments')->where([
            'nds_percent' => 18,
        ])->update([
            'nds_percent' => 20,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('assortments')->where([
            'nds_percent' => 20,
        ])->update([
            'nds_percent' => 18,
        ]);
    }
}
