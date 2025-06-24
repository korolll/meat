<?php

namespace App\Services\Management\Rating;

use App\Models\RatingScore;
use Illuminate\Database\Eloquent\Model;

class RatingScoreFactory implements RatingScoreFactoryContract
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
    public function create($rated, $ratedBy, $ratedThrough, int $value, array $additionalAttributes = []): RatingScore
    {
        $values = array_merge($additionalAttributes, compact('value'));

        return RatingScore::updateOrCreate(
            $this->getAttributes($rated, $ratedBy, $ratedThrough),
            $values
        );
    }

    /**
     * @param Model $rated
     * @param Model $ratedBy
     * @param Model|null $ratedThrough
     * @return array
     */
    protected function getAttributes($rated, $ratedBy, $ratedThrough): array
    {
        $ratedThrough = optional($ratedThrough);

        return [
            'rated_reference_type' => $rated->getMorphClass(),
            'rated_reference_id' => $rated->getKey(),
            'rated_by_reference_type' => $ratedBy->getMorphClass(),
            'rated_by_reference_id' => $ratedBy->getKey(),
            'rated_through_reference_type' => $ratedThrough->getMorphClass(),
            'rated_through_reference_id' => $ratedThrough->getKey(),
        ];
    }
}
