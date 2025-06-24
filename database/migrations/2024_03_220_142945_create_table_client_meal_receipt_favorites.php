<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableClientMealReceiptFavorites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_meal_receipt_favorites', function (Blueprint $table) {
            $table->uuid('client_uuid');
            $table->uuid('meal_receipt_uuid');

            $table->primary(['client_uuid', 'meal_receipt_uuid']);
            $table->index('meal_receipt_uuid');
            $table->index('client_uuid');

            $table->foreign('client_uuid')->references('uuid')->on('clients');
            $table->foreign('meal_receipt_uuid')->references('uuid')->on('meal_receipts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_meal_receipt_favorites');
    }
}
