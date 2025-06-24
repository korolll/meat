<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWriteOffs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('write_offs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->uuid('product_uuid');
            $table->string('write_off_reason_id', 25);
            $table->integer('quantity_old');
            $table->integer('quantity_delta');
            $table->integer('quantity_new');
            $table->text('comment')->nullable();
            $table->timestampTz('created_at');

            $table->index('user_uuid');
            $table->index('product_uuid');
            $table->index('write_off_reason_id');
            $table->index('created_at');

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('product_uuid')->references('uuid')->on('products');
            $table->foreign('write_off_reason_id')->references('id')->on('write_off_reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('write_offs');
    }
}
