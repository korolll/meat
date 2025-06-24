<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransportationPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportation_points', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('transportation_uuid');
            $table->uuid('product_request_uuid');
            $table->string('transportation_point_type_id', 25);
            $table->text('address');
            $table->timestampTz('arrived_at')->nullable();
            $table->integer('order');

            $table->index('transportation_uuid');
            $table->index('product_request_uuid');
            $table->index('transportation_point_type_id');

            $table->foreign('transportation_uuid')->references('uuid')->on('transportations');
            $table->foreign('product_request_uuid')->references('uuid')->on('product_requests');
            $table->foreign('transportation_point_type_id')->references('id')->on('transportation_point_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transportation_points');
    }
}
