<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAssortmentFile140 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assortment_file', function (Blueprint $table) {
            $table->string('file_category_id', 25)
                ->default('assortment-image')
                ->comment('Идентификатор типа файла');

            $table->foreign('file_category_id')->references('id')->on('file_categories');
        });

        Schema::table('assortment_file', function (Blueprint $table) {
            $table->string('file_category_id', 25)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assortment_file', function (Blueprint $table) {
            $table->dropColumn('file_category_id');
        });
    }
}
