<?php

namespace App\Http\Resources;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\RatingScore;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class RatingScoreResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'ratedReference' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'ratedByReference' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
        ]);
    }

    /**
     * @param RatingScore $ratingScore
     * @return array
     */
    public function resource($ratingScore)
    {
        $ratedReference = $ratingScore->ratedReference;
        $ratedByReference = $ratingScore->ratedByReference;

        return [
            'created_at' => $ratingScore->created_at,
            $this->mergeWhen($ratedReference instanceof Assortment, function() use ($ratedReference) {
                return [
                    'assortment_uuid' => $ratedReference->uuid,
                    'assortment_name' => $ratedReference->name,
                ];
            }),
            'value' => $ratingScore->value,
            'comment' => $ratingScore->comment,
            $this->mergeWhen($ratedByReference instanceof Client, function() use ($ratedByReference) {
                return [
                    'client_uuid' => $ratedByReference->uuid,
                    'client_name' => $ratedByReference->name,
                ];
            }),
        ];
    }
}
