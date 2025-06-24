<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientActivePromoFavoriteAssortmentsChangeIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_active_promo_favorite_assortments', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->unique([
                'client_uuid',
                'assortment_uuid',
            ]);

            $table->index(['assortment_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_active_promo_favorite_assortments', function (Blueprint $table) {
            $table->dropUnique([
                'client_uuid',
                'assortment_uuid'
            ]);
            $table->unique(['client_uuid']);
            $table->dropIndex(['assortment_uuid']);
        });
    }
}
