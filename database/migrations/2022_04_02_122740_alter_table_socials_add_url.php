<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSocialsAddUrl extends Migration
{
    public static function upCorrect()
    {
        Schema::table('socials', function (Blueprint $table) {
            $table->string('url');
        });
    }

    public static function downCorrect()
    {
        Schema::table('socials', function (Blueprint $table) {
            $table->dropColumn([
                'url'
            ]);
        });
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CreateTableSocials::upCorrect();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        CreateTableSocials::downCorrect();
    }
}
