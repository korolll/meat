<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDrivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->string('full_name');
            $table->string('password');
            $table->string('email')->unique();
            $table->timestampTz('hired_on');
            $table->timestampTz('fired_on')->nullable();
            $table->string('comment')->nullable();
            $table->string('license_number');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_uuid');
            $table->index('hired_on');
            $table->index('fired_on');
            $table->index('created_at');

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
        Schema::dropIfExists('drivers');
    }
}
