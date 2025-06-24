<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use App\Models\PriceListStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class AssortmentMinPrice implements VirtualColumnContract
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $alias
     * @param array|string|null $excludeUserUuids
     * @param array|string|null $onlyUserUuids
     * @param string|null $customerUserUuid
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function apply($query, string $alias, $excludeUserUuids = [], $onlyUserUuids = [], $customerUserUuid = null)
    {
        /**
         * Запрос для получения минимальной цены по поставщикам
         *
         * @var Builder $innerQueryMinPrices
         */
        $innerQueryMinPrices = User::productSellers()
            ->select([
                // ИД Ассортимента
                'products.assortment_uuid',
                // Минимальная цена
                DB::raw('MIN(COALESCE(price_list_product.price_new, products.price)) as price_min')
            ]);

        if ($onlyUserUuids) {
            $innerQueryMinPrices->whereIn('users.uuid', (array) $onlyUserUuids);
        } elseif ($excludeUserUuids) {
            $innerQueryMinPrices->whereNotIn('users.uuid', (array) $excludeUserUuids);
        }
        $innerQueryMinPrices->leftJoin('price_lists', static function (JoinClause $join) use ($customerUserUuid) {
            $join->on('price_lists.user_uuid', '=', 'users.uuid')
                ->where('price_lists.price_list_status_id', '=', PriceListStatus::CURRENT);
            if ($customerUserUuid === null) {
                $join->whereNull('price_lists.customer_user_uuid');
            } else {
                $join->where('price_lists.customer_user_uuid', '=', $customerUserUuid);
            }
        })->leftJoin('price_list_product', static function (JoinClause $join) {
            $join->on('price_list_product.price_list_uuid', '=', 'price_lists.uuid');
        })->leftJoin('products', static function (JoinClause $join) {
            $join->on('products.uuid', '=', 'price_list_product.product_uuid')
                ->where('products.is_active', '=', true);
        })->groupBy('products.assortment_uuid');

        $query->leftJoinSub($innerQueryMinPrices, $alias, "{$alias}.assortment_uuid", '=', 'assortments.uuid');

        return $query;
    }
}
