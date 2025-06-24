<?php

namespace App\Services\Database\Table;

use Illuminate\Database\Query\Builder;

interface VirtualTableInterface
{
    public function table(string $alias): Builder;
}
