<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTags161 extends Migration
{
    use \App\Services\Framework\Database\Migrations\ManagesComments;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->comment('Идентификатор тега');
            $table->string('name')->comment('Название тега');

            $table->timestampsTz();
            $table->unique('name');
            $table->index('created_at');
        });

        DB::statement('ALTER TABLE tags ADD searchable tsvector NULL');
        DB::statement('CREATE INDEX tags_searchable_index ON tags USING GIN (searchable)');
        $this->commentOnColumn('tags', [
            'searchable' => 'Вектор полнотекстового поиска'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
