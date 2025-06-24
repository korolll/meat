<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomerProductRequestSupplierProductRequest124 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_product_request_supplier_product_request', function (Blueprint $table) {
            $table->uuid('customer_product_request_uuid');
            $table->uuid('supplier_product_request_uuid');

            $table->primary(['customer_product_request_uuid', 'supplier_product_request_uuid']);
            $table->index('customer_product_request_uuid');
            $table->index('supplier_product_request_uuid');

            $table->foreign('customer_product_request_uuid')->references('uuid')->on('product_requests');
            $table->foreign('supplier_product_request_uuid')->references('uuid')->on('product_requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_product_request_supplier_product_request');
    }
}
