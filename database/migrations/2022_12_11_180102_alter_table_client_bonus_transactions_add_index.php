<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientBonusTransactionsAddIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_bonus_transactions', function (Blueprint $table) {
            $table->dropIndex(['client_uuid']);
            $table->index(['client_uuid', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_bonus_transactions', function (Blueprint $table) {
            $table->dropIndex(['client_uuid', 'reason']);
            $table->index('client_uuid');
        });
    }
}
