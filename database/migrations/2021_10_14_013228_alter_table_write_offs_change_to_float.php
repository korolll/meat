<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWriteOffsChangeToFloat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('write_offs', function (Blueprint $table) {
            $table->float('quantity_old')->change();
            $table->float('quantity_delta')->change();
            $table->float('quantity_new')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('write_offs', function (Blueprint $table) {
            $table->integer('quantity_old')->change();
            $table->integer('quantity_delta')->change();
            $table->integer('quantity_new')->change();
        });
    }
}
