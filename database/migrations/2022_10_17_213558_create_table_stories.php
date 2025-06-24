<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->uuid('logo_file_uuid');

            $table->timestampTz('show_from');
            $table->timestampTz('show_to');

            $table->timestampsTz(3);
            $table->softDeletesTz('deleted_at', 3);
            
            $table->foreign('logo_file_uuid')->references('uuid')->on('files');

            $table->index('created_at');
        });

        $queryIndex = '
            CREATE INDEX stories_show_from_to on stories USING gist (tstzrange(show_from, show_to));
        ';
        \Illuminate\Support\Facades\DB::statement($queryIndex);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stories');
    }
}
