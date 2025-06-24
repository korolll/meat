<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientPromotions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_promotions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('client_uuid');
            $table->uuid('user_uuid');
            $table->string('promotion_type')->comment('Идентификатор акции');
            $table->timestampTz('started_at');
            $table->timestampTz('expired_at');

            $table->foreign('client_uuid')
                ->on('clients')
                ->references('uuid')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('user_uuid')
                ->on('users')
                ->references('uuid')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_promotions');
    }
}
