<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoProductRequestSupplierStatusesIssue52 extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'product_request_supplier_statuses';
    }
}
