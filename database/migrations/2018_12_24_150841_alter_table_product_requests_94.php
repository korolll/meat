<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductRequests94 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->string('product_request_delivery_method_id', 25)->default('delivery');
            $table->index('product_request_delivery_method_id');
            $table->foreign('product_request_delivery_method_id')->references('id')->on('product_request_delivery_methods');
        });

        Schema::table('product_requests', function (Blueprint $table) {
            $table->string('product_request_delivery_method_id', 25)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn('product_request_delivery_method_id');
        });
    }
}
