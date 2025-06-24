<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAssortments116 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assortments', function (Blueprint $table) {
            $table->uuid('assortment_brand_uuid')->nullable();

            $table->foreign('assortment_brand_uuid')->references('uuid')->on('assortment_brands');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assortments', function (Blueprint $table) {
            $table->dropColumn('assortment_brand_uuid');
        });
    }
}
