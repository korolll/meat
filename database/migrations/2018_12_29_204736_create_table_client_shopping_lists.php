<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableClientShoppingLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_shopping_lists', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid')->index();
            $table->string('name');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['client_uuid', 'name']);

            $table->foreign('client_uuid')->references('uuid')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_shopping_lists');
    }
}
