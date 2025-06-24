<?php

namespace App\Events;

use App\Models\ProductRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierProductRequestStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var ProductRequest
     */
    public $productRequest;

    /**
     * SupplierProductRequestStatusChanged constructor.
     * @param ProductRequest $productRequest
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }
}
