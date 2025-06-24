<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAssortmentMatrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_matrices', function (Blueprint $table) {
            $table->uuid('user_uuid');
            $table->uuid('assortment_uuid');

            $table->primary(['user_uuid', 'assortment_uuid']);

            $table->foreign('user_uuid')->references('uuid')->on('users');
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
        Schema::dropIfExists('assortment_matrices');
    }
}
