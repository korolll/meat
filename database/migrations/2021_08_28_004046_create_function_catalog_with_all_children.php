<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionCatalogWithAllChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        DB::unprepared("
            create function catalog_with_all_children(uuid) returns setof catalogs
                language sql as
            $$
            SELECT t.*
            FROM (
                WITH RECURSIVE r AS (
                    SELECT *
                    FROM catalogs
                    WHERE uuid = $1
                    UNION
                    SELECT catalogs.*
                    FROM catalogs
                           JOIN r ON catalogs.catalog_uuid = r.uuid
                    )
                    SELECT * FROM r
                ) t
            $$;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS catalog_with_all_children;');
    }
}
