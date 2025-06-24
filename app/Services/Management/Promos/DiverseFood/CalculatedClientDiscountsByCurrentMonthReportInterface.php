<?php

namespace App\Services\Management\Promos\DiverseFood;

use App\Models\PromoDiverseFoodClientDiscount;
use IteratorAggregate;

/**
 * @deprecated
 */
interface CalculatedClientDiscountsByCurrentMonthReportInterface extends IteratorAggregate
{
    /**
     * @return \Traversable|PromoDiverseFoodClientDiscount[]
     */
    public function getIterator();
}
