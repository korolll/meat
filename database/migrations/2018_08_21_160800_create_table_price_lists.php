<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePriceLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->string('name');
            $table->string('price_list_status_id', 25);
            $table->timestampTz('date_from')->nullable();
            $table->timestampTz('date_till')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_uuid');
            $table->index('price_list_status_id');
            $table->index('created_at');

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('price_list_status_id')->references('id')->on('price_list_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_lists');
    }
}
