<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFileUser98 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_user', function (Blueprint $table) {
            $table->uuid('user_uuid');
            $table->uuid('file_uuid');
            $table->string('public_name')->nullable();

            $table->primary(['user_uuid', 'file_uuid']);
            $table->index('user_uuid');
            $table->index('file_uuid');

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('file_uuid')->references('uuid')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_user');
    }
}
