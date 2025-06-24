<?php

namespace App\Services\Management\Promos\DiverseFood;


use Carbon\CarbonInterface;

interface DiscountCalculatorInterface
{
    public function calculate(?CarbonInterface $month = null): void;
}
