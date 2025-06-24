<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAssortmentProperties177 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assortment_properties', function (Blueprint $table) {
            $table->string('assortment_property_data_type_id')->default('string');
            $table->foreign('assortment_property_data_type_id', 'assortment_properties_data_type_fk')
                ->references('id')
                ->on('assortment_property_data_types');
            $table->json('available_values')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assortment_properties', function (Blueprint $table) {
            $table->dropForeign('assortment_properties_data_type_fk');
            $table->dropColumn('available_values');
            $table->dropColumn('assortment_property_data_type_id');
        });
    }
}
