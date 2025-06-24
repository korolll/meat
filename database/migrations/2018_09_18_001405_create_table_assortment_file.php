<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAssortmentFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_file', function (Blueprint $table) {
            $table->uuid('assortment_uuid');
            $table->uuid('file_uuid');

            $table->primary(['assortment_uuid', 'file_uuid']);
            $table->index('assortment_uuid');
            $table->index('file_uuid');

            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
            $table->foreign('file_uuid')->references('uuid')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assortment_file');
    }
}
