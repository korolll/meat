<?php

namespace App\Services\Database\VirtualColumns;

use App\Contracts\Database\VirtualColumnContract;
use App\Exceptions\ServerException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class AssortmentExistsInAssortmentMatrix implements VirtualColumnContract
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $alias
     * @param string|null $userUuid
     * @return \Illuminate\Database\Query\Builder
     * @throws \App\Exceptions\TealsyException
     */
    public static function apply($query, string $alias, $userUuid = null)
    {
        if ($userUuid === null) {
            throw new ServerException('User\'s UUID is missing, something went wrong');
        }

        $query->leftJoin('assortment_matrices', function (JoinClause $join) use ($userUuid) {
            $join->on('assortment_matrices.assortment_uuid', '=', 'assortments.uuid');
            $join->where('assortment_matrices.user_uuid', '=', $userUuid);
        });

        return $query->addSelect(
            DB::raw('assortment_matrices.user_uuid IS NOT NULL AS ' . $alias)
        );
    }
}
