<?php

namespace App\Services\Management\Client\Bonus;

interface MaxBonusesCalculatorInterface
{
    public function calculate(float $total): int;
}
