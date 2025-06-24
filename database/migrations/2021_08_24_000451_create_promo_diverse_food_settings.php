<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoDiverseFoodSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_diverse_food_settings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->integer('count_purchases', false, true)
                ->comment('Количество покупок уникальных товаров в месяц');
            $table->integer('count_rating_scores', false, true)
                ->comment('Количество оценок уникальных товаров в месяц');
            $table->decimal('discount_percent', 19, 2)->comment('скидка');
            $table->boolean('is_enabled')->default(true)->comment('вкл/выкл акции');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_diverse_food_settings');
    }
}
