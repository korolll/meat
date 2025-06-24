<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOrders2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('store_user_uuid')->index();
            $table->uuid('client_uuid')->index();

            $table->string('order_status_id')->index();
            $table->string('order_delivery_type_id')->index();
            $table->string('order_payment_type_id')->index();

            $table->string('client_comment')->nullable();
            $table->string('client_email');
            $table->jsonb('client_address_data')->nullable();
            $table->jsonb('client_payment_data')->nullable();

            $table->boolean('is_paid')->default(false);

            $table->decimal('delivery_price', 19, 2)->nullable()->comment('Цена за доставку');

            $table->decimal('total_price_for_products_with_discount', 19, 2)->comment('ИТОГО за все продукты со скидкой');
            $table->decimal('total_discount_for_products', 19, 2)->comment('ИТОГО за скидки за все продукты');
            $table->decimal('total_price', 19, 2)->comment('ИТОГО за весь заказ');

            $table->double('total_weight')->comment('Итоговый вес продуктов');
            $table->integer('total_quantity')->comment('Итоговое количество продуктов');

            $table->timestampTz('planned_delivery_datetime_from')->nullable();
            $table->timestampTz('planned_delivery_datetime_to')->nullable();

            $table->foreign('order_status_id')->references('id')->on('order_statuses')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('order_delivery_type_id')->references('id')->on('order_delivery_types')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('order_payment_type_id')->references('id')->on('order_payment_types')->onDelete('RESTRICT')->onUpdate('CASCADE');

            $table->foreign('store_user_uuid')->references('uuid')->on('users')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('client_uuid')->references('uuid')->on('clients')->onDelete('RESTRICT')->onUpdate('CASCADE');

            $table->timestampsTz();
        });

        DB::statement('alter table "orders" add column "number" bigserial not null');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
