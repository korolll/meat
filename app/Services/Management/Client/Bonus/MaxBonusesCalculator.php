<?php

namespace App\Services\Management\Client\Bonus;

use App\Services\Money\MoneyHelper;

class MaxBonusesCalculator implements MaxBonusesCalculatorInterface
{
    /**
     * @var float
     */
    private float $maxBonusPercentToPay;

    /**
     * @param float $maxBonusPercentToPay
     */
    public function __construct(float $maxBonusPercentToPay)
    {
        $this->maxBonusPercentToPay = $maxBonusPercentToPay;
    }

    /**
     * @param float $total
     *
     * @return int
     */
    public function calculate(float $total): int
    {
        $maxBonuses = MoneyHelper::percentOf($this->maxBonusPercentToPay, $total);
        return MoneyHelper::toBonus($maxBonuses);
    }
}
