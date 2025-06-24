<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCounters168 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->string('id')->unique()->comment('Идентификатор счетчика');
            $table->string('name')->nullable()->comment('Наименование счетчика');
            $table->double('value')->default('0')->comment('Значение счетчика');
            $table->double('step')->default('1')->comment('Шаг счетчика');

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
        Schema::dropIfExists('counters');
    }
}
