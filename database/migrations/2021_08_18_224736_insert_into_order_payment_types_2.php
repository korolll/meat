<?php

use App\Models\OrderPaymentType;
use Illuminate\Database\Migrations\Migration;

class InsertIntoOrderPaymentTypes2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('order_payment_types')->insert([
            [
                'id' => OrderPaymentType::ID_CASH,
                'name' => 'Оплата наличными'
            ],
            [
                'id' => OrderPaymentType::ID_ONLINE,
                'name' => 'Онлайн оплата'
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
        \Illuminate\Support\Facades\DB::table('order_payment_types')->truncate();
    }
}
