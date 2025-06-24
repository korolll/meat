<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

class MealReceiptIsFavorite implements VirtualColumnContract
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

        $query->leftJoin('client_meal_receipt_favorites', function (JoinClause $join) use ($clientUuid) {
            $join->on('client_meal_receipt_favorites.meal_receipt_uuid', '=', 'meal_receipts.uuid');
            $join->where('client_meal_receipt_favorites.client_uuid', '=', $clientUuid);
        });

        return $query->addSelect(DB::raw('client_meal_receipt_favorites.meal_receipt_uuid is NOT NULL as ' . $alias));
    }
}
