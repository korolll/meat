<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;
use App\Models\Product;

class QuantityOfProductMustBeAtLeastException extends ClientException
{
    /**
     * @param \App\Models\Product $product
     * @param int $minQuantity
     */
    public function __construct(Product $product, int $minQuantity)
    {
        parent::__construct("The quantity of {$product->uuid} must be at least {$minQuantity}");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1018;
    }
}
