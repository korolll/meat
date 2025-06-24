<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoProductRequestCustomerStatusesIssue52 extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'product_request_customer_statuses';
    }
}
