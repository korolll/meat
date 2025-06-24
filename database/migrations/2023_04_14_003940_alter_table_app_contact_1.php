<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAppContact1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_contact', function (Blueprint $table) {
            $table->text('ios_version')->nullable();
            $table->text('android_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_contact', function (Blueprint $table) {
            $table->dropColumn('ios_version');
            $table->dropColumn('android_version');
        });
    }
}
