<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableClientUserFavorites103 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_user_favorites', function (Blueprint $table) {
            $table->uuid('client_uuid');
            $table->uuid('user_uuid');

            $table->primary(['client_uuid', 'user_uuid']);
            $table->index('user_uuid');
            $table->index('client_uuid');

            $table->foreign('client_uuid')->references('uuid')->on('clients');
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
        Schema::dropIfExists('client_user_favorites');
    }
}
