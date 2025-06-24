<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableReceiptLinesAddBonus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receipt_lines', function (Blueprint $table) {
            $table->integer('total_bonus')->nullable()->comment('Количество бонусов (cashback) за продукт');
            $table->integer('paid_bonus')->nullable()->comment('Количество списанных бонусов');
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
                'total_bonus',
                'paid_bonus',
            ]);
        });
    }
}
