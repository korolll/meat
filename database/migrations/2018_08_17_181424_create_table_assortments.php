<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAssortments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('catalog_uuid');
            $table->string('barcode', 13)->unique();
            $table->string('name');
            $table->string('assortment_unit_id', 25);
            $table->string('country_id', 2);
            $table->string('okpo_code', 10)->nullable();
            $table->double('weight');
            $table->double('volume');
            $table->text('ingredients')->nullable();
            $table->text('description')->nullable();
            $table->string('group_barcode', 13)->nullable();
            $table->integer('temperature_min');
            $table->integer('temperature_max');
            $table->string('production_standard_id', 25);
            $table->string('production_standard_number');
            $table->boolean('is_storable');
            $table->unsignedInteger('shelf_life');
            $table->unsignedInteger('nds_percent');
            $table->string('assortment_verify_status_id', 25);

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('catalog_uuid');
            $table->index('assortment_unit_id');
            $table->index('country_id');
            $table->index('production_standard_id');
            $table->index('assortment_verify_status_id');
            $table->index('created_at');

            $table->foreign('catalog_uuid')->references('uuid')->on('catalogs');
            $table->foreign('assortment_unit_id')->references('id')->on('assortment_units');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('production_standard_id')->references('id')->on('production_standards');
            $table->foreign('assortment_verify_status_id')->references('id')->on('assortment_verify_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assortments');
    }
}
