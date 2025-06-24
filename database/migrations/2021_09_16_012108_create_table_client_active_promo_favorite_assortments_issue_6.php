<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientActivePromoFavoriteAssortmentsIssue6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_active_promo_favorite_assortments', function (Blueprint $table) {
            $table->uuid('uuid');

            $table->uuid('client_uuid')->unique();
            $table->uuid('assortment_uuid');

            $table->decimal('discount_percent', 4, 2)->comment('Размер скидки');
            $table->timestampTz('active_from');
            $table->timestampTz('active_to');

            $table->foreign('client_uuid')->references('uuid')->on('clients');
            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');

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
        Schema::dropIfExists('client_active_promo_favorite_assortments');
    }
}
