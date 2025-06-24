<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderTooLowPrice extends ClientException
{
    /**
     * @var float
     */
    private float $minPrice;

    /**
     *
     */
    public function __construct(float $minPrice)
    {
        parent::__construct("You have to add more products to reach minimum price");
        $this->minPrice = $minPrice;
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2011;
    }

    public function getMinPrice(): float
    {
        return $this->minPrice;
    }
}
