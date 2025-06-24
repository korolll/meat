<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePriceListProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_list_product', function (Blueprint $table) {
            $table->uuid('price_list_uuid');
            $table->uuid('product_uuid');
            $table->decimal('price_old', 19, 2)->nullable();
            $table->decimal('price_new', 19, 2)->nullable();

            $table->primary(['price_list_uuid', 'product_uuid']);
            $table->index('price_list_uuid');
            $table->index('product_uuid');

            $table->foreign('price_list_uuid')->references('uuid')->on('price_lists');
            $table->foreign('product_uuid')->references('uuid')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_list_product');
    }
}
