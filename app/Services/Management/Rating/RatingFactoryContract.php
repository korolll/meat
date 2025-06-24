<?php

namespace App\Services\Management\Rating;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Model;

interface RatingFactoryContract
{
    /**
     * @param Model $rated
     * @param string $ratingTypeId
     * @return Rating
     */
    public function updateOrCreate($rated, string $ratingTypeId): Rating;
}
