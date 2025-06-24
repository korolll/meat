<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePromoYellowPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_yellow_prices', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('assortment_uuid')->index();
            $table->decimal('price', 19, 2)->comment('цена по акции');
            $table->boolean('is_enabled')->default(true)->comment('вкл/выкл акции');
            $table->timestampTz('start_at')->comment('начало действия акции');
            $table->timestampTz('end_at')->comment('окончание действия акции');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');

            $table->index(['created_at', 'assortment_uuid']);

        });
        DB::statement("CREATE INDEX start_end_interval on promo_yellow_prices USING gist (tstzrange(start_at, end_at));");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_yellow_prices');
    }
}
