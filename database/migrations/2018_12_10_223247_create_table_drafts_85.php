<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDrafts85 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->uuid('user_uuid');
            $table->string('name');
            $table->jsonb('attributes');
            $table->timestampsTz();

            $table->unique(['user_uuid', 'name']);
            $table->index('user_uuid');
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
        Schema::dropIfExists('drafts');
    }
}
