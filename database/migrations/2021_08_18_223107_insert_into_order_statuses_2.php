<?php

use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;

class InsertIntoOrderStatuses2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('order_statuses')->insert([
            [
                'id' => OrderStatus::ID_NEW,
                'name' => 'Оформлен'
            ],
            [
                'id' => OrderStatus::ID_COLLECTING,
                'name' => 'Собирается'
            ],
            [
                'id' => OrderStatus::ID_COLLECTED,
                'name' => 'Собран'
            ],
            [
                'id' => OrderStatus::ID_DELIVERING,
                'name' => 'Доставляется'
            ],
            [
                'id' => OrderStatus::ID_DONE,
                'name' => 'Выполнен'
            ],
            [
                'id' => OrderStatus::ID_CANCELLED,
                'name' => 'Отменен'
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
        \Illuminate\Support\Facades\DB::table('order_statuses')->truncate();
    }
}
