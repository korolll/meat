<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRatings56 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('reference_type');
            $table->uuid('reference_id');
            $table->string('rating_type_id', 25);
            $table->float('value');
            $table->jsonb('additional_attributes')->nullable();

            $table->timestampsTz();

            $table->unique(['reference_type', 'reference_id', 'rating_type_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('rating_type_id');
            $table->index('created_at');

            $table->foreign('rating_type_id')->references('id')->on('rating_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
