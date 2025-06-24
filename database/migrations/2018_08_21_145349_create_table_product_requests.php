<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_requests', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('customer_user_uuid');
            $table->uuid('supplier_user_uuid');
            $table->uuid('delivery_user_uuid')->nullable();
            $table->string('product_request_customer_status_id', 25);
            $table->string('product_request_supplier_status_id', 25);
            $table->string('product_request_delivery_status_id', 25);
            $table->decimal('price', 19, 2);
            $table->double('weight');
            $table->double('volume');
            $table->uuid('transportation_uuid')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('customer_user_uuid');
            $table->index('supplier_user_uuid');
            $table->index('delivery_user_uuid');
            $table->index('product_request_customer_status_id');
            $table->index('product_request_supplier_status_id');
            $table->index('product_request_delivery_status_id');
            $table->index('transportation_uuid');
            $table->index('created_at');

            $table->foreign('customer_user_uuid')->references('uuid')->on('users');
            $table->foreign('supplier_user_uuid')->references('uuid')->on('users');
            $table->foreign('delivery_user_uuid')->references('uuid')->on('users');
            $table->foreign('product_request_customer_status_id')->references('id')->on('product_request_customer_statuses');
            $table->foreign('product_request_supplier_status_id')->references('id')->on('product_request_supplier_statuses');
            $table->foreign('product_request_delivery_status_id')->references('id')->on('product_request_delivery_statuses');
            $table->foreign('transportation_uuid')->references('uuid')->on('transportations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_requests');
    }
}
