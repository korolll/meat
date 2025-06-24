<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPreRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_pre_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_uuid');
            $table->uuid('product_request_uuid');
            $table->uuid('product_uuid');
            $table->unsignedInteger('quantity');
            $table->timestampTz('delivery_date');
            $table->timestampTz('confirmed_delivery_date');
            $table->unsignedTinyInteger('status');

            $table->timestampsTz();

            $table->foreign('product_request_uuid')->references('uuid')->on('product_requests');
            $table->foreign('product_uuid')->references('uuid')->on('products');
            $table->foreign('user_uuid')->references('uuid')->on('users');

            $table->unique(['user_uuid', 'product_request_uuid', 'product_uuid']);

            $table->index(['delivery_date', 'confirmed_delivery_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_pre_requests');
    }
}
