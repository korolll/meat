<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixCreateProductPreRequestCustomerSupplierRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pre_request_customer_supplier_relation', function (Blueprint $table) {
            DB::unprepared('
                alter table "product_pre_request_customer_supplier_relation" drop constraint if exists "product_pre_request_customer_supplier_relation_customer_user_uu";
                alter table "product_pre_request_customer_supplier_relation" drop constraint if exists "product_pre_request_customer_supplier_relation_customer_user_uu";
                alter table "product_pre_request_customer_supplier_relation" drop constraint if exists "product_pre_request_customer_supplier_relation_supplier_user_uu";
            ');

            $table->foreign('customer_user_uuid', 'fk_customer_user_uuid')->references('uuid')->on('users');
            $table->foreign('supplier_user_uuid', 'fk_supplier_user_uuid')->references('uuid')->on('users');
            $table->unique(['customer_user_uuid', 'supplier_user_uuid'], 'uk_customer_user_uuid__supplier_user_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_pre_request_customer_supplier_relation', function (Blueprint $table) {
            DB::unprepared('
                alter table "product_pre_request_customer_supplier_relation" drop constraint if exists "uk_customer_user_uuid__supplier_user_uuid";
                alter table "product_pre_request_customer_supplier_relation" drop constraint if exists "fk_customer_user_uuid";
                alter table "product_pre_request_customer_supplier_relation" drop constraint if exists "fk_supplier_user_uuid";
            ');

            $table->foreign('customer_user_uuid', 'product_pre_request_customer_supplier_relation_customer_user_uu')
                ->references('uuid')
                ->on('users');
            $table->foreign('supplier_user_uuid', 'product_pre_request_customer_supplier_relation_supplier_user_uu')
                ->references('uuid')
                ->on('users');

            $table->unique(['customer_user_uuid', 'supplier_user_uuid'], 'product_pre_request_customer_supplier_relation_customer_user_uu');
        });
    }
}
