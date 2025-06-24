<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientShoppingLists2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_shopping_lists', function (Blueprint $table) {
            $table->dropUnique(['client_uuid', 'name']);
            $table->unique(['client_uuid', 'name', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_shopping_lists', function (Blueprint $table) {
            $table->dropUnique(['client_uuid', 'name', 'deleted_at']);
            $table->unique(['client_uuid', 'name']);
        });
    }
}
