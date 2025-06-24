<?php

namespace App\Events;

use App\Models\RatingScore;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RatingScoreSaved
{
    use Dispatchable, SerializesModels;

    /**
     * @var RatingScore
     */
    public $ratingScore;

    /**
     * @param RatingScore $ratingScore
     */
    public function __construct(RatingScore $ratingScore)
    {
        $this->ratingScore = $ratingScore;
    }
}
