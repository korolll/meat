<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;
use App\Models\Product;

class QuantityOfProductMustBeMultipleException extends ClientException
{
    /**
     * @param \App\Models\Product $product
     * @param int $multipleOf
     */
    public function __construct(Product $product, int $multipleOf)
    {
        parent::__construct("The quantity of {$product->uuid} must be a multiple of {$multipleOf}");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1019;
    }
}
