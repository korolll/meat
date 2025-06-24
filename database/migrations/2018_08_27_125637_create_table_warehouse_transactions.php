<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWarehouseTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('product_uuid');
            $table->integer('quantity_old');
            $table->integer('quantity_delta');
            $table->integer('quantity_new');
            $table->string('reference_type');
            $table->uuid('reference_id');
            $table->timestampTz('created_at');

            $table->index('product_uuid');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');

            $table->foreign('product_uuid')->references('uuid')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_transactions');
    }
}
