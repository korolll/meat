<?php

use Illuminate\Database\Migrations\Migration;

class CreateFunctionCatalogWithAllParents169 extends Migration
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
            create function catalog_with_all_parents(uuid) returns setof catalogs
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
                           JOIN r ON catalogs.uuid = r.catalog_uuid
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
        DB::unprepared('DROP FUNCTION IF EXISTS catalog_with_all_parents;');
    }
}
