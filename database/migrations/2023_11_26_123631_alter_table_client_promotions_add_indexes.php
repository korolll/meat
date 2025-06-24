<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientPromotionsAddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        try {
//            DB::statement('CREATE EXTENSION btree_gist;');
//        } catch (\Throwable $exception) {
//
//        }

        Schema::table('client_promotions', function (Blueprint $table) {
            $table->index('client_uuid');
            $table->index('user_uuid');

            DB::statement("CREATE INDEX client_promotions_client_active_gist on client_promotions USING gist (client_uuid, tstzrange(started_at, expired_at));");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_promotions', function (Blueprint $table) {
            $table->dropIndex(['client_uuid']);
            $table->dropIndex(['user_uuid']);
            $table->dropIndex('client_promotions_active_index');
        });
    }
}
