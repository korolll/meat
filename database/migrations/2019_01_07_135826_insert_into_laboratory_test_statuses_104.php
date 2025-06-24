<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoLaboratoryTestStatuses104 extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'laboratory_test_statuses';
    }
}
