<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDiscountForbiddenAssortments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_forbidden_assortments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('assortment_uuid')->unique();

            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount_forbidden_assortments');
    }
}
