<?php

namespace App\Services\Management\Rating;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

abstract class AverageRatingCalculator extends RatingCalculator
{
    /**
     * @param Model $rated
     * @param Builder $ratingScoresQuery
     * @return float
     */
    protected function process($rated, Builder $ratingScoresQuery): float
    {
        return $ratingScoresQuery->avg('value');
    }
}
