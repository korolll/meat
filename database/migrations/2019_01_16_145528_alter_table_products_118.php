<?php

use \Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProducts118 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER TABLE products ALTER COLUMN delivery_weekdays TYPE JSONB');
        DB::unprepared("ALTER TABLE products ALTER COLUMN delivery_weekdays SET DEFAULT '[0,1,2,3,4,5,6]'::jsonb");

        Schema::table('products', function (Blueprint $table) {
            $table->index('delivery_weekdays', null, 'GIN');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['delivery_weekdays']);
        });

        DB::unprepared('ALTER TABLE products ALTER COLUMN delivery_weekdays TYPE JSON');
        DB::unprepared("ALTER TABLE products ALTER COLUMN delivery_weekdays SET DEFAULT '[0,1,2,3,4,5,6]'::json");
    }
}
