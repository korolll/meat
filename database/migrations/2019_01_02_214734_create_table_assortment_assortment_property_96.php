<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssortmentAssortmentProperty96 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_assortment_property', function (Blueprint $table) {
            $table->uuid('assortment_uuid');
            $table->uuid('assortment_property_uuid');
            $table->string('value');

            $table->primary(['assortment_uuid', 'assortment_property_uuid']);
            $table->index('assortment_uuid');
            $table->index('assortment_property_uuid');

            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
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
        Schema::dropIfExists('assortment_assortment_property');
    }
}
