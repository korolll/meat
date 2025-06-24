<?php

namespace App\Services\Management\ProductRequest;

use App\Models\Product;
use Carbon\CarbonInterface;

interface ProductRequestExpectedDeliveryDateValidatorContract
{
    /**
     * @param CarbonInterface $startOfMInDeliveryDate
     * @return ProductRequestExpectedDeliveryDateValidatorContract
     */
    public function setStartOfMinDeliveryDate(CarbonInterface $startOfMInDeliveryDate): ProductRequestExpectedDeliveryDateValidatorContract;

    /**
     * @param CarbonInterface $expectedDeliveryDate
     * @param array|Product[] $products
     * @return bool
     * @throws \App\Exceptions\TealsyException
     */
    public function validate(CarbonInterface $expectedDeliveryDate, array $products): bool;
}
