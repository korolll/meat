<?php

namespace App\Services\Management\Rating;

use App\Models\RatingScore;
use Illuminate\Database\Eloquent\Model;

interface RatingScoreFactoryContract
{
    /**
     * @param Model $rated
     * @param Model $ratedBy
     * @param Model|null $ratedThrough
     * @param int $value
     * @param array $additionalAttributes
     * @return RatingScore
     * @throws \Throwable
     */
    public function create($rated, $ratedBy, $ratedThrough, int $value, array $additionalAttributes = []): RatingScore;
}
