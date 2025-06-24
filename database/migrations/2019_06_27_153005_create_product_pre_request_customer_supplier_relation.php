<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPreRequestCustomerSupplierRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_pre_request_customer_supplier_relation', function (Blueprint $table) {
            $table->uuid('customer_user_uuid');
            $table->uuid('supplier_user_uuid');

            $table->foreign('customer_user_uuid', 'product_pre_request_customer_supplier_relation_customer_fk')->references('uuid')->on('users');
            $table->foreign('supplier_user_uuid', 'product_pre_request_customer_supplier_relation_supplier_fk')->references('uuid')->on('users');

            $table->unique(['customer_user_uuid', 'supplier_user_uuid'], 'product_pre_request_customer_supplier_relation_ui');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_pre_request_customer_supplier_relation');
    }
}
