<?php

namespace App\Services\Framework\Database;

use App\Services\Framework\Database\Query\Grammars\PostgresGrammar;
use Staudenmeir\LaravelCte\Connections\PostgresConnection as BasePostgresConnection;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new PostgresGrammar());
    }
}
