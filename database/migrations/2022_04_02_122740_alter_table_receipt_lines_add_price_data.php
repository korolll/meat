<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableReceiptLinesAddPriceData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receipt_lines', function (Blueprint $table) {
            $table->decimal('price_with_discount', 19, 2)->nullable()->comment('Цена со скидкой (итоговая)');
            $table->decimal('discount', 19, 2)->nullable()->comment('Размер скидки');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receipt_lines', function (Blueprint $table) {
            $table->dropColumn([
                'price_with_discount',
                'discount',
            ]);
        });
    }
}
