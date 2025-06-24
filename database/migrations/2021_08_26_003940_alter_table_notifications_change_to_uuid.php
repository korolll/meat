<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableNotificationsChangeToUuid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('notifiable_id');
            $table->uuid('notifiable_uuid');
        });

        $addIndex = "
            CREATE INDEX notification_notifiable_index
                ON notifications (notifiable_type, notifiable_uuid, created_at DESC)
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
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('notifiable_uuid');
            $table->string('notifiable_id');
        });

        $addIndex = "
            CREATE INDEX notification_notifiable_index
                ON notifications (notifiable_type, notifiable_id, created_at DESC)
        ";
        \Illuminate\Support\Facades\DB::statement($addIndex);
    }
}
