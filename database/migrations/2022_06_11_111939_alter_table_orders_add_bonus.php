<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOrdersAddBonus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('total_bonus')->nullable()->comment('Количество расчитанных бонусов за всю продукцию');
            $table->integer('bonus_to_charge')->nullable()->comment('Количество бонусов к зачислению');
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total_bonus',
                'bonus_to_charge',
                'paid_bonus',
            ]);
        });
    }
}
