<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionInTheShopLastPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_in_the_shop_last_purchases', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid');
            $table->uuid('assortment_uuid');
            $table->timestampsTz();
            $table->timestampTz('delete_after')->comment('Чистка таблицы. Дата окончания жизненного цикла строки');

            $table->unique([
                'client_uuid',
                'assortment_uuid'
            ]);

            $table->foreign('client_uuid')
                ->on('clients')
                ->references('uuid')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('assortment_uuid')
                ->on('assortments')
                ->references('uuid')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_in_the_shop_last_purchases');
    }
}
