<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_payments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid')->index();

            $table->integer('amount');
            $table->integer('order_status')->nullable()->index();
            $table->string('generated_order_uuid');
            $table->string('binding_id')->nullable()->index();

            $table->foreign('client_uuid')->references('uuid')->on('clients');

            $table->morphs('related_reference');
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
        Schema::dropIfExists('client_payments');
    }
}
