<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoProductRequestCustomerStatuses extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'product_request_customer_statuses';
    }
}
