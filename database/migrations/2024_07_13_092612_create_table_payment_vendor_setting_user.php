<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePaymentVendorSettingUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_vendor_setting_user', function (Blueprint $table) {
            $table->uuid('payment_vendor_setting_uuid');
            $table->uuid('user_uuid')->index();
            $table->boolean('is_active')->default(false);

            $table->primary([
                'payment_vendor_setting_uuid',
                'user_uuid',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_vendor_setting_user');
    }
}
