<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePromotionInTheShopLastPurchasesChangeToCatalogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotion_in_the_shop_last_purchases', function (Blueprint $table) {
            $table->dropUnique([
                'client_uuid',
                'assortment_uuid',
            ]);

            $table->dropColumn('assortment_uuid');
            $table->uuid('catalog_uuid')->index();

            $table->foreign('catalog_uuid')
                ->on('catalogs')
                ->references('uuid')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->unique([
                'client_uuid',
                'catalog_uuid',
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
        Schema::table('promotion_in_the_shop_last_purchases', function (Blueprint $table) {
            $table->dropUnique([
                'client_uuid',
                'catalog_uuid',
            ]);

            $table->dropColumn('catalog_uuid');
            $table->uuid('assortment_uuid')->index();

            $table->foreign('assortment_uuid')
                ->on('assortments')
                ->references('uuid')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->unique([
                'client_uuid',
                'assortment_uuid',
            ]);
        });
    }
}
