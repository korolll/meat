<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMealReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_receipts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->string('name');
            $table->string('section');
            $table->string('title');
            $table->string('description');
            $table->json('ingredients');

            $table->uuid('file_uuid');
            $table->foreign('file_uuid')->references('uuid')->on('files');

            $table->timestampsTz(3);
            $table->softDeletesTz('deleted_at', 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_receipts');
    }
}
