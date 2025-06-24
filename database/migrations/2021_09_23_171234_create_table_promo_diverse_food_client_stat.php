<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePromoDiverseFoodClientStat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_diverse_food_client_stats', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('client_uuid');
            $table->string('month')->index();

            $table->unique([
                'client_uuid',
                'month',
            ]);

            $table->integer('purchased_count');
            $table->integer('rated_count');

            $table->foreign('client_uuid')->references('uuid')->on('clients');
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
        Schema::dropIfExists('promo_diverse_food_client_stats');
    }
}
