<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptLine;

class ReceiptWarehouseTransactionProducer extends AbstractWarehouseTransactionProducer
{
    /**
     * @var Receipt
     */
    protected $model;

    /**
     * @var \Illuminate\Support\Collection|ReceiptLine[]
     */
    protected $lines;

    /**
     * @return \Illuminate\Support\Collection|Product[]
     */
    public function getProducts()
    {
        $this->lines = $this->model->receiptLines()->with('product')->get();

        return $this->lines->pluck('product')->filter();
    }

    /**
     * @param Product $product
     * @return int
     */
    public function getProductQuantityDelta(Product $product)
    {
        $line = $this->lines->firstWhere('product_uuid', $product->uuid);

        return $line ? $line->quantity * -1 : 0;
    }
}
