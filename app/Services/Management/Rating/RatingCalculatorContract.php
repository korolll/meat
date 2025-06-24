<?php

namespace App\Services\Management\Rating;

use Illuminate\Database\Eloquent\Model;

interface RatingCalculatorContract
{
    /**
     * @param Model $rated
     * @return float
     */
    public function calculate($rated): float;
}
