<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableWarehouseTransactions91 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_transactions', function (Blueprint $table) {
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
        Schema::table('warehouse_transactions', function (Blueprint $table) {
            $table->integer('quantity_old')->change();
            $table->integer('quantity_delta')->change();
            $table->integer('quantity_new')->change();
        });
    }
}
