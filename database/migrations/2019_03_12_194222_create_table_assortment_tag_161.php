<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssortmentTag161 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_tag', function (Blueprint $table) {
            $table->uuid('tag_uuid')->comment('Идентификатор тега');
            $table->uuid('assortment_uuid')->comment('Идентификатор нуменклатуры');

            $table->primary(['tag_uuid', 'assortment_uuid']);
            $table->index('tag_uuid');
            $table->index('assortment_uuid');

            $table->foreign('tag_uuid')->references('uuid')->on('tags')->onDelete('CASCADE');
            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assortment_tag');
    }
}
