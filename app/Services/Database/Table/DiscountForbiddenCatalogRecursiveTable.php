<?php

namespace App\Services\Database\Table;

use App\Models\Catalog;
use App\Models\DiscountForbiddenCatalog;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DiscountForbiddenCatalogRecursiveTable implements VirtualTableInterface
{
    /**
     * @param string $alias
     *
     * @return mixed
     */
    public function table(string $alias): Builder
    {
        $baseQuery = DiscountForbiddenCatalog::select('catalog_uuid');
        /** @var \Illuminate\Database\Query\Builder $recursive */
        $recursive = Catalog::select('catalogs.uuid as catalog_uuid')
            ->join($alias, $alias . '.catalog_uuid', '=', 'catalogs.catalog_uuid')
            ->withTrashed();

        $baseQuery->union($recursive);
        return DB::table($alias)->withRecursiveExpression($alias, $baseQuery);
    }
}
