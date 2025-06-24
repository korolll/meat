<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransportations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->date('date');
            $table->uuid('car_uuid');
            $table->uuid('driver_uuid');
            $table->string('transportation_status_id', 25);
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_uuid');
            $table->index('car_uuid');
            $table->index('driver_uuid');
            $table->index('transportation_status_id');
            $table->index('created_at');

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('car_uuid')->references('uuid')->on('cars');
            $table->foreign('driver_uuid')->references('uuid')->on('drivers');
            $table->foreign('transportation_status_id')->references('id')->on('transportation_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transportations');
    }
}
