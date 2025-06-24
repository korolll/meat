<?php

namespace App\Events;

use App\Models\ProductRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerProductRequestStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var ProductRequest
     */
    public $productRequest;

    /**
     * CustomerProductRequestStatusChanged constructor.
     * @param ProductRequest $productRequest
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }
}
