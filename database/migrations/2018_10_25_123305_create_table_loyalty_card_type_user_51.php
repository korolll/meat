<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLoyaltyCardTypeUser51 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_card_type_user', function (Blueprint $table) {
            $table->uuid('loyalty_card_type_uuid');
            $table->uuid('user_uuid');

            $table->primary(['user_uuid', 'loyalty_card_type_uuid']);

            $table->foreign('loyalty_card_type_uuid')->references('uuid')->on('loyalty_card_types');
            $table->foreign('user_uuid')->references('uuid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loyalty_card_type_user');
    }
}
