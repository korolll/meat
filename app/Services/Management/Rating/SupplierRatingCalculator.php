<?php

namespace App\Services\Management\Rating;

use App\Models\User;
use Illuminate\Database\Query\Builder;

class SupplierRatingCalculator extends AverageRatingCalculator
{
    /**
     * @param User $rated
     * @return Builder
     */
    protected function getRatingScoresQuery($rated): Builder
    {
        return $rated->supplierRatingScores()->toBase();
    }
}
