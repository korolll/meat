<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoPriceListStatuses extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'price_list_statuses';
    }
}
