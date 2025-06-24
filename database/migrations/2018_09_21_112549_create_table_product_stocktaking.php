<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductStocktaking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_stocktaking', function (Blueprint $table) {
            $table->uuid('stocktaking_uuid');
            $table->uuid('product_uuid');
            $table->string('write_off_reason_id', 25)->nullable();
            $table->integer('quantity_old');
            $table->integer('quantity_new');
            $table->text('comment')->nullable();

            $table->primary(['stocktaking_uuid', 'product_uuid']);
            $table->index('stocktaking_uuid');
            $table->index('product_uuid');

            $table->foreign('stocktaking_uuid')->references('uuid')->on('stocktakings');
            $table->foreign('product_uuid')->references('uuid')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_stocktaking');
    }
}
