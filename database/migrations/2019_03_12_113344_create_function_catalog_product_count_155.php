<?php

use Illuminate\Database\Migrations\Migration;

class CreateFunctionCatalogProductCount155 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        // Подсчет кол-ва продуктов в каталоге
        DB::unprepared("
            CREATE FUNCTION catalog_product_count(uuid) RETURNS INTEGER AS
            $$ SELECT COALESCE(SUM(c), 0)::integer
               FROM (WITH RECURSIVE r AS (
                    SELECT
                        catalogs.uuid
                    FROM
                        catalogs
                    WHERE
                        catalogs.uuid = $1
                    UNION 
                    SELECT
                        catalogs.uuid
                    FROM
                        catalogs
                    JOIN r ON catalogs.catalog_uuid = r.uuid)
                    SELECT
                        COUNT(assortments.*) c
                    FROM
                        r
                    JOIN products ON products.catalog_uuid = r.uuid
                    JOIN assortments ON assortments.uuid = products.assortment_uuid
                    WHERE
                        assortments.assortment_verify_status_id IN ('approved', 'new') AND
                        assortments.deleted_at IS NULL
                    GROUP BY
                        r.uuid
                    ) t $$
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
        DB::unprepared('DROP FUNCTION IF EXISTS catalog_product_count;');
    }
}
