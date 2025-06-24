<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

class StoreDeliveryPrice implements VirtualColumnContract
{
    public const EMPTY_UUID_VALUE = 'empty';
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $alias
     * @param string $clientUuid
     * @return \Illuminate\Database\Query\Builder
     */
    public static function apply($query, string $alias, ?string $clientUuid = '')
    {
        $clientUuid = is_null($clientUuid) ? self::EMPTY_UUID_VALUE : $clientUuid;
        $clientUuid = "'" . addslashes($clientUuid) . "'";
        return $query->addSelect(DB::raw($clientUuid . ' as ' . $alias));
    }
}
