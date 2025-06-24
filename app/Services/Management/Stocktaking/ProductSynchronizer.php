<?php

namespace App\Services\Management\Stocktaking;

use App\Models\Stocktaking;
use App\Services\Management\Stocktaking\Contracts\ProductSynchronizerContract;
use Illuminate\Support\Facades\DB;

class ProductSynchronizer implements ProductSynchronizerContract
{
    /**
     * @param Stocktaking $stocktaking
     * @param array $onlyThisCatalogUuids
     * @return int
     */
    public function synchronize(Stocktaking $stocktaking, array $onlyThisCatalogUuids = [])
    {
        $query = "
            INSERT INTO
                product_stocktaking (stocktaking_uuid, product_uuid, quantity_old, quantity_new)
            SELECT
                ?, products.uuid, products.quantity, products.quantity
            FROM
                products
            JOIN 
                assortments ON assortments.uuid = products.assortment_uuid
            WHERE
                products.user_uuid = ?
                {$this->makeCatalogUuidWhere($onlyThisCatalogUuids)}
            ON CONFLICT DO NOTHING
        ";

        return DB::affectingStatement($query, [$stocktaking->uuid, $stocktaking->user_uuid]);
    }

    /**
     * @param array $onlyThisCatalogUuids
     * @return string
     */
    protected function makeCatalogUuidWhere(array $onlyThisCatalogUuids = [])
    {
        if (empty($onlyThisCatalogUuids)) {
            return null;
        }

        $clause = implode("','", $onlyThisCatalogUuids);

        return "AND assortments.catalog_uuid IN ('{$clause}')";
    }
}
