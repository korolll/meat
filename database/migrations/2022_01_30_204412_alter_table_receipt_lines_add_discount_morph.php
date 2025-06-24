<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableReceiptLinesAddDiscountMorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receipt_lines', function (Blueprint $table) {

            // Morph
            $table->string("discountable_type")->nullable()->comment('Ссылка на таблицу со моделью скидки');
            $table->uuid("discountable_uuid")->nullable()->comment('UUID модели скидки');
            $table->index(["discountable_type", "discountable_uuid"]);
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
                'discountable_type',
                'discountable_uuid'
            ]);
        });
    }
}
