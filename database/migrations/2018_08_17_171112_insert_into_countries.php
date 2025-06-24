<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoCountries extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'countries';
    }
}
