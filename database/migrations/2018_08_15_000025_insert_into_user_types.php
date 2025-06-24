<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoUserTypes extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'user_types';
    }
}
