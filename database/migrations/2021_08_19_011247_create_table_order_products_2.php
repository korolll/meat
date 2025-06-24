<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOrderProducts2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('order_uuid')->index();
            $table->uuid('product_uuid')->index();

            $table->integer('quantity');

            $table->decimal('price_with_discount', 19, 2)->comment('Цена со скидкой (итоговая)');
            $table->decimal('discount', 19, 2)->comment('Размер скидки');

            $table->decimal('total_amount_with_discount', 19, 2)->comment('Суммарная цена за позицию с учетом количества');
            $table->decimal('total_discount', 19, 2)->comment('Суммарный размер скидки');

            $table->double('total_weight');

            // Morph
            $table->string("discountable_type")->nullable()->comment('Ссылка на таблицу со моделью скидки');
            $table->uuid("discountable_uuid")->nullable()->comment('UUID модели скидки');
            $table->index(["discountable_type", "discountable_uuid"]);

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_products');
    }
}
