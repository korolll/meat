<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->unsignedInteger('receipt_package_id');
            $table->unsignedInteger('id');
            $table->uuid('loyalty_card_uuid')->nullable();
            $table->uuid('loyalty_card_type_uuid')->nullable();
            $table->string('loyalty_card_number', 20)->nullable();
            $table->decimal('total', 19, 2);
            $table->timestampTz('created_at');
            $table->timestampTz('received_at');

            $table->unique(['user_uuid', 'id', 'created_at']);
            $table->index('loyalty_card_uuid');
            $table->index(['loyalty_card_type_uuid', 'loyalty_card_number']);
            $table->index('created_at');
            $table->index('received_at');

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('loyalty_card_uuid')->references('uuid')->on('loyalty_cards');
            $table->foreign('loyalty_card_type_uuid')->references('uuid')->on('loyalty_card_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
