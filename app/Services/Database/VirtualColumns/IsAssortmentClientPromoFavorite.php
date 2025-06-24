<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use App\Exceptions\ServerException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IsAssortmentClientPromoFavorite implements VirtualColumnContract
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $alias
     * @param array                              $clientUuids
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws ServerException
     */
    public static function apply($query, string $alias, $clientUuids = [])
    {
        $clientUuids = array_filter(Arr::wrap($clientUuids));
        if (! $clientUuids) {
            return $query->addSelect(DB::raw('1=0 as ' . $alias));
        }

        $query->leftJoin('client_active_promo_favorite_assortments', function ($join) use ($clientUuids) {
            $join->on('assortments.uuid', '=', 'client_active_promo_favorite_assortments.assortment_uuid');
            $join->whereIn('client_active_promo_favorite_assortments.client_uuid', (array)$clientUuids);

            $now = now();
            $join->where('active_from', '<', $now);
            $join->where('active_to', '>', $now);
        });

        return $query->addSelect(DB::raw('client_active_promo_favorite_assortments.assortment_uuid is NOT NULL as ' . $alias));
    }
}
