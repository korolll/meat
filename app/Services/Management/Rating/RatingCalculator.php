<?php

namespace App\Services\Management\Rating;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

abstract class RatingCalculator implements RatingCalculatorContract
{
    /**
     * Количество знаков после запятой
     */
    const RATING_PRECISION = 2;

    /**
     * @param Model $rated
     * @return Builder
     */
    abstract protected function getRatingScoresQuery($rated): Builder;

    /**
     * @param Model $rated
     * @param Builder $ratingScoresQuery
     * @return float
     */
    abstract protected function process($rated, Builder $ratingScoresQuery): float;

    /**
     * @param Model $rated
     * @return float
     */
    public function calculate($rated): float
    {
        $ratingScoresQuery = $this->getRatingScoresQuery($rated);

        return round($this->process($rated, $ratingScoresQuery), static::RATING_PRECISION);
    }
}
