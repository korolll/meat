<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertIntoPromoDescriptionsSystemPromos extends Migration
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
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'name' => 'Любимый продукт',
                'title' => 'Любимый продукт',
                'description' => '',
                'discount_type' => \App\Models\ClientActivePromoFavoriteAssortment::class,
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'name' => 'Я в Магазине',
                'title' => 'Я в Магазине',
                'description' => '',
                'discount_type' => \App\Models\ClientPromotion::class,
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'name' => 'Желтые ценники',
                'title' => 'Желтые ценники',
                'description' => '',
                'discount_type' => \App\Models\PromoYellowPrice::class,
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'name' => 'Разнообразное питание',
                'title' => 'Разнообразное питание',
                'description' => '',
                'discount_type' => \App\Models\PromoDiverseFoodClientDiscount::class,
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
            ->whereNotNull('discount_type')
            ->delete();
    }
}
