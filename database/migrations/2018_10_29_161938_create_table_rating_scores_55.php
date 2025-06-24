<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRatingScores55 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_scores', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('rated_reference_type');
            $table->uuid('rated_reference_id');
            $table->string('rated_by_reference_type');
            $table->uuid('rated_by_reference_id');
            $table->string('rated_through_reference_type')->nullable();
            $table->uuid('rated_through_reference_id')->nullable();
            $table->integer('value');
            $table->jsonb('additional_attributes')->nullable();

            $table->timestampsTz();

            $table->unique([
                'rated_reference_type',
                'rated_reference_id',
                'rated_by_reference_type',
                'rated_by_reference_id',
                'rated_through_reference_type',
                'rated_through_reference_id',
            ]);

            $table->index(['rated_reference_type', 'rated_reference_id']);
            $table->index(['rated_by_reference_type', 'rated_by_reference_id']);
            $table->index(['rated_through_reference_type', 'rated_through_reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rating_scores');
    }
}
