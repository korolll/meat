<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->uuid('assortment_uuid');
            $table->uuid('catalog_uuid');
            $table->integer('quantum');
            $table->integer('min_quantum_in_order');
            $table->integer('quantity');
            $table->decimal('price', 19, 2)->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_uuid');
            $table->index('assortment_uuid');
            $table->index('catalog_uuid');
            $table->index('created_at');
            $table->unique(['user_uuid', 'assortment_uuid']);

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
            $table->foreign('catalog_uuid')->references('uuid')->on('catalogs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
