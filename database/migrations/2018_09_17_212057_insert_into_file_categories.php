<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoFileCategories extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'file_categories';
    }
}
