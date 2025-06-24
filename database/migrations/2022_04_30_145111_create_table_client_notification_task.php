<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientNotificationTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_notification_task', function (Blueprint $table) {
            $table->uuid('client_uuid');
            $table->uuid('notification_task_uuid');
            $table->primary([
                'notification_task_uuid',
                'client_uuid',
            ]);

            $table->index('client_uuid');

            $table->foreign('client_uuid')->references('uuid')->on('clients');
            $table->foreign('notification_task_uuid')->references('uuid')->on('notification_tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_notification_task');
    }
}
