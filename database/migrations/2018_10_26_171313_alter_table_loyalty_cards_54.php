<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableLoyaltyCards54 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loyalty_cards', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->index();
            $table->foreign('client_uuid')->references('uuid')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loyalty_cards', function (Blueprint $table) {
            $table->dropColumn('client_uuid');
        });
    }
}
