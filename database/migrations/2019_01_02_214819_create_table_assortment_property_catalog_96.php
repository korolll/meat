<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssortmentPropertyCatalog96 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_property_catalog', function (Blueprint $table) {
            $table->uuid('catalog_uuid');
            $table->uuid('assortment_property_uuid');

            $table->primary(['catalog_uuid', 'assortment_property_uuid']);
            $table->index('catalog_uuid');
            $table->index('assortment_property_uuid');

            $table->foreign('catalog_uuid')->references('uuid')->on('catalogs');
            $table->foreign('assortment_property_uuid')->references('uuid')->on('assortment_properties');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assortment_property_catalog');
    }
}
