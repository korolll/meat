<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableOnboardings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onboardings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('title');
            $table->unsignedSmallInteger('sort_number')->nullable()->comment('Пользовательская сортировка');
            
            $table->uuid('logo_file_uuid');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('logo_file_uuid');
            $table->index('created_at');

            $table->foreign('logo_file_uuid')->references('uuid')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onboardings');
    }
}
