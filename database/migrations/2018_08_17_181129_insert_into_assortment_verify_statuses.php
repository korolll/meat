<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoAssortmentVerifyStatuses extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'assortment_verify_statuses';
    }
}
