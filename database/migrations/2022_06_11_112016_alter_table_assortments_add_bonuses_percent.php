<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAssortmentsAddBonusesPercent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assortments', function (Blueprint $table) {
            $table->decimal('bonus_percent', 4, 2)->nullable()->comment('Процент начисления бонусов (cashback)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assortments', function (Blueprint $table) {
            $table->dropColumn('bonus_percent');
        });
    }
}
