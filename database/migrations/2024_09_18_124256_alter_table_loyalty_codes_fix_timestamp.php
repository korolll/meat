<?php

use Illuminate\Database\Migrations\Migration;

class AlterTableLoyaltyCodesFixTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
            ALTER TABLE loyalty_codes 
            ALTER COLUMN expires_on TYPE timestamptz
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('
            ALTER TABLE loyalty_codes 
            ALTER COLUMN expires_on TYPE timestamp
        ');
    }
}
