<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePaymentVendorSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_vendor_settings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('payment_vendor_id')->index();

            $table->text('config');
            $table->foreign('payment_vendor_id')->references('id')->on('payment_vendors');

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
        Schema::dropIfExists('payment_vendor_settings');
    }
}
