<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\Clients\API\CatalogCollectionResponse;
use App\Models\Catalog;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index(Request $request)
    {
//        $this->authorize('index', Catalog::class);

        $query = Catalog::public()
            ->select([
                'catalogs.*',
                DB::raw('(SELECT cc.uuid FROM catalogs as cc WHERE cc.catalog_uuid = catalogs.uuid LIMIT 1) is NULL as is_final_level')
            ]);

        if ($storeUuid = $request->get('store_uuid')) {
            $query
                ->addSelect(DB::raw('user_catalog_product_counts.product_count as assortments_count_in_store'))
                ->addSelect(DB::raw('user_catalog_product_counts.properties as assortments_properties_in_store'))
                ->addSelect(DB::raw('user_catalog_product_counts.tags as assortments_tags_in_store'))
                ->join('user_catalog_product_counts', function (JoinClause $join) use ($storeUuid) {
                    $join->on('user_catalog_product_counts.catalog_uuid', 'catalogs.uuid');
                    $join->where('user_catalog_product_counts.user_uuid', $storeUuid);
                });
        }

        return CatalogCollectionResponse::create($query);
    }
}
