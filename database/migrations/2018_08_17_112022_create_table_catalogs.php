<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCatalogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid')->nullable();
            $table->uuid('catalog_uuid')->nullable();
            $table->string('name');
            $table->unsignedSmallInteger('level');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_uuid');
            $table->index('catalog_uuid');
            $table->index('created_at');

            $table->foreign('user_uuid')->references('uuid')->on('users');
        });

        // В pgsql не хочет работать, если перенести выше, где и должно быть
        Schema::table('catalogs', function (Blueprint $table) {
            $table->foreign('catalog_uuid')->references('uuid')->on('catalogs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogs');
    }
}
