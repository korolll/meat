<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientMealReceiptLikes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_meal_receipt_likes', function (Blueprint $table) {
            $table->uuid('client_uuid');
            $table->uuid('meal_receipt_uuid')->index();

            $table->primary([
                'client_uuid',
                'meal_receipt_uuid'
            ]);

            $table->foreign('client_uuid')->references('uuid')->on('clients')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('meal_receipt_uuid')->references('uuid')->on('meal_receipts')->onDelete('RESTRICT')->onUpdate('CASCADE');

            $table->boolean('is_positive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_meal_receipt_likes');
    }
}
