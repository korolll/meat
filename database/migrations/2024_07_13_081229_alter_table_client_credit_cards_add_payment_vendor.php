<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientCreditCardsAddPaymentVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_credit_cards', function (Blueprint $table) {
            $table->uuid('payment_vendor_setting_uuid')->nullable()->index();
            $table->foreign('payment_vendor_setting_uuid')->references('uuid')->on('payment_vendor_settings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_credit_cards', function (Blueprint $table) {
            $table->dropColumn('payment_vendor_setting_uuid');
        });
    }
}
