<?php

namespace App\Services\Management\Rating;

use App\Models\Assortment;
use Illuminate\Database\Query\Builder;

class AssortmentRatingCalculator extends WeightedRatingCalculator
{
    /**
     * @param Assortment $rated
     * @return Builder
     */
    protected function getRatingScoresQuery($rated): Builder
    {
        return $rated->ratingScores()->latest()->limit(100)->toBase();
    }
}
