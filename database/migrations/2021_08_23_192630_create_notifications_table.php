<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->string('notifiable_id');
            $table->json('data');
            $table->timestampTz('read_at', 3)->nullable();
            $table->timestampsTz(3);
        });

        $addIndex = "
            CREATE INDEX notification_notifiable_index
                ON notifications (notifiable_type, notifiable_id, created_at DESC)
        ";
        \Illuminate\Support\Facades\DB::statement($addIndex);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
