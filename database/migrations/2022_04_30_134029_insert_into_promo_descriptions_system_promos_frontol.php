<?php

use App\Models\PromoDescription;
use Illuminate\Database\Migrations\Migration;

class InsertIntoPromoDescriptionsSystemPromosFrontol extends Migration
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
                'uuid' => PromoDescription::VIRTUAL_FRONTOL_DISCOUNT_UUID,
                'name' => 'Локальная скидка Frontol',
                'title' => 'Локальная скидка Frontol',
                'description' => '',
                'discount_type' => PromoDescription::class,
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
            ->where('discount_type', PromoDescription::class)
            ->delete();
    }
}
