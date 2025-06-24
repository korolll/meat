<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use App\Models\PriceList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class ProductPrice implements VirtualColumnContract
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $alias
     * @param null|string                        $customerUserUuid
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function apply($query, string $alias, $customerUserUuid = null)
    {
        $priceListsSubQuery = static::makePriceListSubQuery($customerUserUuid);
        $query->leftJoin('price_list_product', function (JoinClause $join) use ($priceListsSubQuery) {
            $join->on('price_list_product.product_uuid', '=', 'products.uuid');
            $join->whereIn('price_list_product.price_list_uuid', $priceListsSubQuery);
        });

        return $query->addSelect(
            DB::raw('price_list_product.price_new AS ' . $alias)
        );
    }

    /**
     * @param string|null $customerUserUuid
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected static function makePriceListSubQuery(?string $customerUserUuid)
    {
        $priceListsQuery = PriceList::current();
        $priceListsQuery->where(function (Builder $query) use ($customerUserUuid) {
            $query->whereNull('price_lists.customer_user_uuid');
            if (! is_null($customerUserUuid)) {
                $query->orWhere('price_lists.customer_user_uuid', '=', $customerUserUuid);
            }
        });

        $priceListsQuery->select('uuid');
        $priceListsQuery->selectRaw('ROW_NUMBER() OVER (PARTITION BY price_lists.user_uuid ORDER BY price_lists.customer_user_uuid NULLS LAST) n');

        $priceListsQuery = DB::query()
            ->fromSub($priceListsQuery->toBase(), '_')
            ->select('_.uuid')
            ->where('_.n', '=', 1);

        return $priceListsQuery;
    }
}
