<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsersAddStoreFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_parking')->default(false)->comment('Есть парковка');
            $table->boolean('has_ready_meals')->default(false)->comment('Есть готовая еда');
            $table->boolean('has_atms')->default(false)->comment('Есть банкоматы');
            $table->uuid('image_uuid')->nullable()->comment('Изображение');

            $table->foreign('image_uuid')->references('uuid')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'has_parking',
                'has_ready_meals',
                'has_atms',
                'image_uuid',
            ]);
        });
    }
}
