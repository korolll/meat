<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoDiverseFoodClientDiscounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_diverse_food_client_discounts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid')->index();
            $table->decimal('discount_percent')->comment('процент скидки');
            $table->timestampTz('start_at');
            $table->timestampTz('end_at');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['created_at', 'client_uuid']);
            $table->foreign('client_uuid')->references('uuid')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_diverse_food_client_discounts');
    }
}
