<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableNotificationTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_tasks', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->string('title_template');
            $table->text('body_template');
            $table->json('options')->nullable();

            $table->timestampTz('execute_at')->nullable();
            $table->timestampTz('taken_to_work_at')->nullable();
            $table->timestampTz('executed_at')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_tasks');
    }
}
