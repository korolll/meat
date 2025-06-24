<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->decimal('less_zone_price', 19, 2)->nullable()->comment('Цена за доставку при малом расстоянии');
            $table->decimal('between_zone_price', 19, 2)->nullable()->comment('Цена за доставку при среднем расстоянии');
            $table->decimal('more_zone_price', 19, 2)->nullable()->comment('Цена за доставку при большом расстоянии');
            $table->integer('less_zone_distance')->nullable()->comment('Малое расстояние доставки');
            $table->integer('between_zone_distance')->nullable()->comment('Среднее расстояние доставки');
            $table->integer('more_zone_distance')->nullable()->comment('Большое расстояние доставки');
            $table->integer('max_zone_distance')->nullable()->comment('Максимальное расстояние доставки');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_delivery_zones');
    }
};
