<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAssortmentsFix79 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assortments', function (Blueprint $table) {
            $table->string('group_barcode', 200)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assortments', function (Blueprint $table) {
            $table->string('group_barcode', 200)->nullable(false)->change();
        });
    }
}
