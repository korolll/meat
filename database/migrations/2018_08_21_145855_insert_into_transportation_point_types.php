<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoTransportationPointTypes extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'transportation_point_types';
    }
}
