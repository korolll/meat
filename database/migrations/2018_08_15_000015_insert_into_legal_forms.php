<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoLegalForms extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'legal_forms';
    }
}
