<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDiscountForbiddenCatalogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_forbidden_catalogs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('catalog_uuid')->unique();

            $table->foreign('catalog_uuid')->references('uuid')->on('catalogs');

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
        Schema::dropIfExists('discount_forbidden_catalogs');
    }
}
