<?php

namespace App\Services\Management\Rating;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

abstract class WeightedRatingCalculator extends RatingCalculator
{
    /**
     * @param Model $rated
     * @param Builder $ratingScoresQuery
     * @return float
     */
    protected function process($rated, Builder $ratingScoresQuery): float
    {
        $ratingScores = $ratingScoresQuery->get([
            'value',
            DB::raw("additional_attributes->>'weight' as weight"),
        ]);

        $sumProduct = $ratingScores->sum(function ($ratingScore) {
            return $ratingScore->value * $ratingScore->weight;
        });

        return $sumProduct / $ratingScores->sum('weight');
    }
}
