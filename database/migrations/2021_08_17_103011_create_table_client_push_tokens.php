<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientPushTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_push_tokens', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('client_uuid')->index();
            $table->foreign('client_uuid')->references('uuid')->on('clients')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->timestampsTz(3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_push_tokens');
    }
}
