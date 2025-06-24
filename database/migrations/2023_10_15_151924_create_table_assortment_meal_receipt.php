<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAssortmentMealReceipt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_meal_receipt', function (Blueprint $table) {
            $table->uuid('meal_receipt_uuid');
            $table->uuid('assortment_uuid');

            $table->primary(['meal_receipt_uuid', 'assortment_uuid']);
            $table->index('assortment_uuid');

            $table->foreign('meal_receipt_uuid')->references('uuid')->on('meal_receipts');
            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_receipt_product');
    }
}
