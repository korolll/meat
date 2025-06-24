<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserCatalogProductCounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_catalog_product_counts', function (Blueprint $table) {
            $table->uuid('user_uuid');
            $table->uuid('catalog_uuid');
            $table->primary([
                'user_uuid',
                'catalog_uuid',
            ]);
            $table->integer('product_count');

            $table->foreign('user_uuid')->references('uuid')->on('users');
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
        Schema::dropIfExists('user_catalog_product_counts');
    }
}
