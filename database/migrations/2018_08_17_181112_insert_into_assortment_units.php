<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoAssortmentUnits extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'assortment_units';
    }
}
