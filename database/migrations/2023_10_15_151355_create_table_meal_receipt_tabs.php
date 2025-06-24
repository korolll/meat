<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMealReceiptTabs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_receipt_tabs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('meal_receipt_uuid')->index();
            $table->foreign('meal_receipt_uuid')->references('uuid')->on('meal_receipts');

            $table->integer('sequence');
            $table->integer('duration');
            $table->string('button_title')->nullable();
            $table->string('text_color')->nullable();
            $table->string('url')->nullable()->index();
            $table->string('title');
            $table->text('text');

            $table->uuid('file_uuid');
            $table->foreign('file_uuid')->references('uuid')->on('files');

            $table->timestampsTz(3);
            $table->softDeletesTz('deleted_at', 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_receipt_tabs');
    }
}
