<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientDeliveryAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_delivery_addresses', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('client_uuid')->index();
            $table->string('title');
            $table->string('city');
            $table->string('street');
            $table->string('house');
            $table->integer('entrance')->nullable();
            $table->integer('apartment_number')->nullable();
            $table->integer('floor')->nullable();
            $table->string('intercom_code')->nullable();

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
        Schema::dropIfExists('client_delivery_addresses');
    }
}
