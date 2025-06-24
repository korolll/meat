<?php

use App\Services\Framework\Database\Migrations\MigrationCSV;

class InsertIntoProductRequestDeliveryMethods extends MigrationCSV
{
    /**
     * @return string
     */
    protected function getTable()
    {
        return 'product_request_delivery_methods';
    }
}
