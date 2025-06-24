<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePromoDiverseFoodClientStatAssortments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_diverse_food_client_stat_assortments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('promo_diverse_food_client_stat_uuid');
            $table->uuid('assortment_uuid');

            $table->unique([
                'promo_diverse_food_client_stat_uuid',
                'assortment_uuid'
            ]);

            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
            $table->foreign('promo_diverse_food_client_stat_uuid', 'promo_diverse_food_client_stat_assortments_base_fk')->references('uuid')->on('promo_diverse_food_client_stats')->onDelete('CASCADE');

            $table->boolean('is_rated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_diverse_food_client_stat_assortments');
    }
}
