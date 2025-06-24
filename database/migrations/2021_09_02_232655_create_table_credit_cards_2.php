<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCreditCards2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_credit_cards', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid')->index();
            $table->uuid('virtual_order_uuid');
            $table->uuid('generated_order_uuid')->nullable();
            $table->string('card_mask')->nullable();
            $table->string('binding_id')->nullable();

            $table->foreign('client_uuid')->references('uuid')->on('clients');

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
        Schema::dropIfExists('client_credit_cards');
    }
}
