<?php

use Illuminate\Database\Migrations\Migration;

class CreateFunctionCatalogAssortmentCountByUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        // Подсчет кол-ва ассортимента в каталоге в магазине
        DB::unprepared("
            CREATE FUNCTION catalog_assortment_count_by_user(uuid, uuid) RETURNS INTEGER AS
            $$ SELECT COALESCE(SUM(c), 0)::integer
               FROM (
                    WITH RECURSIVE r AS (
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
                        JOIN r ON catalogs.catalog_uuid = r.uuid
                    )
                    SELECT
                        COUNT(assortments.*) c
                    FROM
                        r
                    JOIN assortments ON assortments.catalog_uuid = r.uuid
                    JOIN products ON products.user_uuid = $2 AND products.assortment_uuid = assortments.uuid
                    WHERE
                        assortments.assortment_verify_status_id = 'approved' AND
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
        DB::unprepared('DROP FUNCTION IF EXISTS catalog_assortment_count_by_user;');
    }
}
