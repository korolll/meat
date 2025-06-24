<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

class StoreIsFavorite implements VirtualColumnContract
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $alias
     * @param string $clientUuid
     * @return \Illuminate\Database\Query\Builder
     */
    public static function apply($query, string $alias, string $clientUuid = '')
    {
        // Избранный магазин
        if (! $clientUuid) {
            return $query->addSelect(DB::raw('1=0 as ' . $alias));
        }

        $query->leftJoin('client_user_favorites', function (JoinClause $join) use ($clientUuid) {
            $join->on('client_user_favorites.user_uuid', '=', 'users.uuid');
            $join->where('client_user_favorites.client_uuid', '=', $clientUuid);
        });

        return $query->addSelect(DB::raw('client_user_favorites.user_uuid is NOT NULL as ' . $alias));
    }
}
