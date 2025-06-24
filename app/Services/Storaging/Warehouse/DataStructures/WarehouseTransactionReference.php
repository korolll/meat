<?php

namespace App\Services\Storaging\Warehouse\DataStructures;

use App\Services\Framework\HasStaticMakeMethod;

class WarehouseTransactionReference
{
    use HasStaticMakeMethod;

    /**
     * @var string
     * @see warehouse_transactions.reference_type
     */
    public $type;

    /**
     * @var string
     * @see warehouse_transactions.reference_id
     */
    public $id;

    /**
     * @param string $type
     * @param string $id
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }
}
