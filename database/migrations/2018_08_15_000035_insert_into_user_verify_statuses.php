<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoUserVerifyStatuses extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'user_verify_statuses';
    }
}
