<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePaymentVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_vendors', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestampsTz();
        });

        $now = now();
        DB::table('payment_vendors')->insert([
            [
                'id' => \App\Models\PaymentVendor::ID_SBERBANK,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => \App\Models\PaymentVendor::ID_YOOKASSA,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_vendors');
    }
}
