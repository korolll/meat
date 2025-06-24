<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromoYellowPriceUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_yellow_price_user', function (Blueprint $table) {
            $table->uuid('user_uuid')->comment('идентификатор магазина для которого доступна акция');
            $table->uuid('promo_yellow_price_uuid')->comment('идентификатор акции');
            $table->primary(['user_uuid', 'promo_yellow_price_uuid']);

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('promo_yellow_price_uuid')->references('uuid')->on('promo_yellow_prices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_yellow_price_user');
    }
}
