<?php

namespace App\Listeners;

use App\Events\RatingScoreSaved;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\RatingScore;
use App\Models\RatingType;
use App\Services\Management\Rating\RatingFactoryContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;

class CalculateRating implements ShouldQueue
{
    /**
     * @param RatingScoreSaved $event
     */
    public function handle(RatingScoreSaved $event)
    {
        if (($ratingTypeId = $this->getRatingTypeId($event->ratingScore)) === null) {
            return;
        }

        $this->getRatingFactory($event->ratingScore)->updateOrCreate(
            $event->ratingScore->ratedReference,
            $ratingTypeId
        );
    }

    /**
     * @param RatingScore $ratingScore
     * @return RatingFactoryContract
     */
    protected function getRatingFactory(RatingScore $ratingScore): ?RatingFactoryContract
    {
        $class = Relation::getMorphedModel($ratingScore->rated_reference_type);

        return app(config("app.ratings.{$class}.factory"));
    }

    /**
     * @param RatingScore $ratingScore
     * @return null|string
     */
    protected function getRatingTypeId(RatingScore $ratingScore): ?string
    {
        if ($ratingScore->rated_through_reference_type === CustomerProductRequest::MORPH_TYPE_ALIAS) {
            return RatingType::ID_SUPPLIER;
        }

        if ($ratingScore->rated_through_reference_type === SupplierProductRequest::MORPH_TYPE_ALIAS) {
            return RatingType::ID_CUSTOMER;
        }

        return RatingType::ID_COMMON;
    }
}
