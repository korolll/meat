<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use App\Exceptions\ServerException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IsAssortmentClientFavorite implements VirtualColumnContract
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

        $query->leftJoin('assortment_client_favorites', function ($join) use ($clientUuids) {
            $join->on('assortments.uuid', '=', 'assortment_client_favorites.assortment_uuid');
            $join->whereIn('assortment_client_favorites.client_uuid', (array)$clientUuids);
        });

        return $query->addSelect(DB::raw('assortment_client_favorites.assortment_uuid is NOT NULL as ' . $alias));
    }
}
