<?php

use App\Models\SystemOrderSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTableSystemOrderSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_order_settings', function (Blueprint $table) {
            $table->string('id');
            $table->string('value')->nullable();
            $table->timestampsTz();
        });

        $now = now();
        DB::table('system_order_settings')->insert([
           [
               'id' => SystemOrderSetting::ID_MIN_PRICE,
               'value' => null,
               'created_at' => $now,
               'updated_at' => $now,
           ],
           [
               'id' => SystemOrderSetting::ID_DELIVERY_THRESHOLD,
               'value' => env('APP_ORDER_PRICE_DELIVERY_FREE_THRESHOLD', 1000),
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
        Schema::dropIfExists('system_order_settings');
    }
}
