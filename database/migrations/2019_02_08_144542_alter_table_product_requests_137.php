<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductRequests137 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_requests', function (Blueprint $table) {
            //
            $table->string('customer_comment')->nullable()->comment('Комментарий заказчика');
            $table->string('supplier_comment')->nullable()->comment('Комментарий поставщика');
            $table->string('delivery_comment')->nullable()->comment('Комментарий доставки');
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
            //
            $table->dropColumn([
                'customer_comment',
                'supplier_comment',
                'delivery_comment',
            ]);
        });
    }
}
