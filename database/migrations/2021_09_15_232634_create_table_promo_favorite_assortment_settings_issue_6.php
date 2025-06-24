<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePromoFavoriteAssortmentSettingsIssue6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_favorite_assortment_settings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->decimal('threshold_amount', 19, 2)->comment('Порог суммы для акцивации');
            $table->unsignedSmallInteger('number_of_sum_days')->comment('Количество дней для расчета суммы');
            $table->unsignedSmallInteger('number_of_active_days')->comment('Количество дней действия скидки после выбора');

            $table->decimal('discount_percent', 4, 2)->comment('Размер скидки');
            $table->boolean('is_enabled')->default(true)->comment('Вкл/выкл акции');

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_favorite_assortments');
    }
}
