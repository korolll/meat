<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientPromoFavoriteAssortmentVariantsIssue6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_promo_favorite_assortment_variants', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('client_uuid');
            $table->unique('client_uuid');
            $table->foreign('client_uuid')->references('uuid')->on('clients');

            $table->timestampTz('can_be_activated_till');
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
        Schema::dropIfExists('client_promo_favorite_assortment_variants');
    }
}
