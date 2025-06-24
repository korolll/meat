<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStoryTabs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('story_tabs', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('story_id')->index();
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('RESTRICT')->onUpdate('CASCADE');

            $table->integer('duration');
            $table->string('button_title')->nullable();
            $table->string('url')->nullable()->index();

            $table->string('title');
            $table->text('text');
            $table->uuid('logo_file_uuid');
            $table->foreign('logo_file_uuid')->references('uuid')->on('files');

            $table->timestampsTz(3);
            $table->softDeletesTz('deleted_at', 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('story_tabs');
    }
}
