<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUsers98 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position')->nullable();
            $table->string('bank_correspondent_account', 20)->nullable();
            $table->string('bank_current_account', 20)->nullable();
            $table->string('bank_identification_code', 9)->nullable();
            $table->string('bank_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'bank_correspondent_account',
                'bank_current_account',
                'bank_identification_code',
                'bank_name',
            ]);
        });
    }
}
