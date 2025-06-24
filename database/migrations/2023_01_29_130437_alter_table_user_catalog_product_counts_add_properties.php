<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUserCatalogProductCountsAddProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_catalog_product_counts', function (Blueprint $table) {
            $table->jsonb('properties')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_catalog_product_counts', function (Blueprint $table) {
            $table->dropColumn('properties');
        });
    }
}
