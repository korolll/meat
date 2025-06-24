<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionInTheShop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_in_the_shop_assortments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_promotion_uuid');
            $table->uuid('assortment_uuid');
            $table->string('assortment_mark')->comment('Метка товара на который предоставляется скидка, например: "Новинка", "Распродажа", "Давно не покупали"');

            $table->unique([
                'client_promotion_uuid',
                'assortment_uuid'
            ]);

            $table->foreign('client_promotion_uuid')
                ->on('client_promotions')
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
        Schema::dropIfExists('promotion_in_the_shop_assortments');
    }
}
