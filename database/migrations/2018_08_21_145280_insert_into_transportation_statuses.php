<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoTransportationStatuses extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'transportation_statuses';
    }
}
