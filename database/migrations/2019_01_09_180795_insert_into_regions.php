<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoRegions extends MigrationCSV
{
    protected $needGenerateUuid = true;

    /**
     * @return string
     */
    protected function getTable()
    {
        return 'regions';
    }

    /**
     * @return void
     */
    public function down()
    {
        DB::table($this->getTable())->delete();
    }

    /**
     * @return string
     */
    protected function getKeyName()
    {
        return 'uuid';
    }
}
