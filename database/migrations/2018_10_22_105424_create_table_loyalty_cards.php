<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLoyaltyCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('loyalty_card_type_uuid');
            $table->string('number', 20);

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['loyalty_card_type_uuid', 'number']);
            $table->index('created_at');

            $table->foreign('loyalty_card_type_uuid')->references('uuid')->on('loyalty_card_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loyalty_cards');
    }
}
