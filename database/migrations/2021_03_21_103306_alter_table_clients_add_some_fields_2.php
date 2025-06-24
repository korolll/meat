<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClientsAddSomeFields2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->uuid('selected_store_user_uuid')->nullable()->comment('Выбранный магазин');
            $table->boolean('consent_to_service_newsletter')->default(false)->comment('Согласие на сервисную рассылку');
            $table->boolean('consent_to_receive_promotional_mailings')->default(false)->comment('Согласие на получение рекламной рассылки');

            $table->foreign('selected_store_user_uuid')->references('uuid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'user_uuid',
                'consent_to_service_newsletter',
                'consent_to_receive_promotional_mailings',
            ]);
        });
    }
}
