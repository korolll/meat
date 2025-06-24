<?php

use App\Models\OrderDeliveryType;
use Illuminate\Database\Migrations\Migration;

class InsertIntoOrderDeliveryTypes2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('order_delivery_types')->insert([
            [
                'id' => OrderDeliveryType::ID_DELIVERY,
                'name' => 'Доставка'
            ],
            [
                'id' => OrderDeliveryType::ID_PICKUP,
                'name' => 'Самовывоз'
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
        \Illuminate\Support\Facades\DB::table('order_delivery_types')->truncate();
    }
}
