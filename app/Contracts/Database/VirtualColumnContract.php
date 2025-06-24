<?php

namespace App\Contracts\Database;

interface VirtualColumnContract
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $alias
     * @return \Illuminate\Database\Query\Builder
     */
    public static function apply($query, string $alias);
}
