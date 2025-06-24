<?php

namespace App\Events;

use App\Models\RatingScore;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RatingScoreCreated extends EventWithMoment
{
    use Dispatchable, SerializesModels;

    /**
     * @var RatingScore
     */
    public RatingScore $ratingScore;

    /**
     * @param RatingScore $ratingScore
     */
    public function __construct(RatingScore $ratingScore)
    {
        parent::__construct();
        $this->ratingScore = $ratingScore;
    }
}
