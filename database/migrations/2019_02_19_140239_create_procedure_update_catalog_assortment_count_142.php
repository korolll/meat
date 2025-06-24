<?php

use Illuminate\Database\Migrations\Migration;

class CreateProcedureUpdateCatalogAssortmentCount142 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        // Обнловление кол-ва ассортимента в каталоге
        DB::unprepared("
            CREATE FUNCTION update_catalog_assortment_count(uuid) RETURNS void AS 
            $$ WITH RECURSIVE r AS (
                   SELECT
                      catalogs.uuid,
                      catalogs.catalog_uuid
                   FROM
                      catalogs
                   WHERE
                      catalogs.uuid = $1
                   UNION 
                   SELECT
                      catalogs.uuid,
                      catalogs.catalog_uuid
                   FROM
                      catalogs
                   JOIN r ON catalogs.uuid = r.catalog_uuid
               )
               UPDATE
                 catalogs c
               SET
                 assortments_count = catalog_assortment_count(c.uuid)
               WHERE
                  c.uuid IN (SELECT r.uuid FROM r) $$
            LANGUAGE SQL;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS update_catalog_assortment_count;');
    }
}
