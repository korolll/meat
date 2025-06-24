<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReceiptLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipt_lines', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('receipt_uuid');
            $table->uuid('product_uuid')->nullable();
            $table->string('barcode', 13);
            $table->integer('quantity');
            $table->decimal('total', 19, 2);

            $table->index('receipt_uuid');
            $table->index('product_uuid');

            $table->foreign('receipt_uuid')->references('uuid')->on('receipts');
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
        Schema::dropIfExists('receipt_lines');
    }
}
