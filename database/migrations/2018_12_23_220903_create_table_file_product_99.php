<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFileProduct99 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_product', function (Blueprint $table) {
            $table->uuid('product_uuid');
            $table->uuid('file_uuid');
            $table->string('public_name')->nullable();

            $table->primary(['product_uuid', 'file_uuid']);
            $table->index('product_uuid');
            $table->index('file_uuid');

            $table->foreign('product_uuid')->references('uuid')->on('products');
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
        Schema::dropIfExists('file_product');
    }
}
