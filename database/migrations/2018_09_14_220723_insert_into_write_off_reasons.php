<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoWriteOffReasons extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'write_off_reasons';
    }
}
