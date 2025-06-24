<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductProductRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_product_request', function (Blueprint $table) {
            $table->uuid('product_request_uuid');
            $table->uuid('product_uuid');
            $table->integer('quantity');
            $table->decimal('price', 19, 2);
            $table->double('weight');
            $table->double('volume');

            $table->primary(['product_request_uuid', 'product_uuid']);
            $table->index('product_request_uuid');
            $table->index('product_uuid');

            $table->foreign('product_request_uuid')->references('uuid')->on('product_requests');
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
        Schema::dropIfExists('product_product_request');
    }
}
