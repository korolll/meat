<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientPromotionsAddDiscount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_promotions', function (Blueprint $table) {
            $table->decimal('discount_percent', 4, 2)->comment('Размер скидки');

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_promotions', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percent',
                'created_at',
                'updated_at',
            ]);
        });
    }
}
