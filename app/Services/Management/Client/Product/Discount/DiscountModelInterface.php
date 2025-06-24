<?php

namespace App\Services\Management\Client\Product\Discount;

use Carbon\CarbonInterface;

interface DiscountModelInterface
{
    /**
     * @return \Carbon\CarbonInterface
     */
    public function getActiveFrom(): CarbonInterface;

    /**
     * @return \Carbon\CarbonInterface
     */
    public function getActiveTo(): CarbonInterface;
}
