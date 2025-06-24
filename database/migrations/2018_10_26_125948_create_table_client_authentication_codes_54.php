<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientAuthenticationCodes54 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_authentication_codes', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid');
            $table->string('code', 25);
            $table->timestampTz('created_at');

            $table->index(['client_uuid', 'code']);
            $table->index('created_at');

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
        Schema::dropIfExists('client_authentication_codes');
    }
}
