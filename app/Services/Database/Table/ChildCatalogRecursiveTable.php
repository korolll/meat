<?php

namespace App\Services\Database\Table;

use App\Models\Catalog;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ChildCatalogRecursiveTable implements VirtualTableInterface
{
    /**
     * @var array
     */
    private array $catalogUuids;

    /**
     * @param array $catalogUuids
     */
    public function __construct(array $catalogUuids)
    {
        $this->catalogUuids = $catalogUuids;
    }

    /**
     * @param string $alias
     *
     * @return mixed
     */
    public function table(string $alias): Builder
    {
        $baseQuery = Catalog::select('uuid')
            ->whereIn('uuid', $this->catalogUuids);

        /** @var \Illuminate\Database\Query\Builder $recursive */
        $recursive = Catalog::select('catalogs.uuid')
            ->join($alias, $alias . '.uuid', '=', 'catalogs.catalog_uuid')
            ->withTrashed();

        $baseQuery->union($recursive);
        return DB::table($alias)->withRecursiveExpression($alias, $baseQuery);
    }
}
