<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientBonusTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_bonus_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid')->index();

            $table->nullableUuidMorphs('related_reference');
            $table->string('reason');

            $table->integer('quantity_old');
            $table->integer('quantity_new');
            $table->integer('quantity_delta');

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
        Schema::dropIfExists('client_bonus_transactions');
    }
}
