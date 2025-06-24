<?php

use App\Models\Promo\PromoDescriptionFirstOrder;
use Illuminate\Database\Migrations\Migration;

class InsertIntoPromoDescriptionsFirstOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = now();
        \Illuminate\Support\Facades\DB::table('promo_descriptions')->insert([
            [
                'uuid' => PromoDescriptionFirstOrder::UUID,
                'name' => 'Скидка за первый заказ',
                'title' => 'Скидка за первый заказ',
                'description' => '',
                'discount_type' => PromoDescriptionFirstOrder::class,
                'created_at' => $date,
                'updated_at' => $date,
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
        \Illuminate\Support\Facades\DB::table('promo_descriptions')
            ->where('uuid', PromoDescriptionFirstOrder::UUID)
            ->delete();
    }
}
