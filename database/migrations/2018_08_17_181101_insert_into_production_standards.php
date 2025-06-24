<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoProductionStandards extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'production_standards';
    }
}
