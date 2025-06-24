<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssortmentClientShoppingList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_client_shopping_list', function (Blueprint $table) {
            $table->uuid('client_shopping_list_uuid')->index();
            $table->uuid('assortment_uuid')->index();
            $table->integer('quantity')->nullable();

            $table->primary(['client_shopping_list_uuid', 'assortment_uuid']);

            $table->foreign('client_shopping_list_uuid')->references('uuid')->on('client_shopping_lists');
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
        Schema::dropIfExists('assortment_client_shopping_list');
    }
}
