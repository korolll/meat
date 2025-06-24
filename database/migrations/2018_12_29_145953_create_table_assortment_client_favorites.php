<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssortmentClientFavorites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_client_favorites', function (Blueprint $table) {
            $table->uuid('client_uuid')->index();
            $table->uuid('assortment_uuid')->index();

            $table->primary(['client_uuid', 'assortment_uuid']);

            $table->foreign('client_uuid')->references('uuid')->on('clients');
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
        Schema::dropIfExists('assortment_client_favorites');
    }
}
