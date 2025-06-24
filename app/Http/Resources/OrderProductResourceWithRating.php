<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Relations\Relation;

class OrderProductResourceWithRating extends OrderProductResourceCollection
{
    /**
     * @param mixed  $resource
     * @param string $prefix
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        $resource->loadMissing([
            $prefix . 'rating' => function (Relation $query) {
                return $query->select([
                    'uuid',
                    'rated_through_reference_type',
                    'rated_through_reference_id',
                    'value',
                    'additional_attributes',
                ]);
            },
        ]);

        parent::loadMissing($resource, $prefix);
    }

    /**
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return array
     */
    public function resource($orderProduct)
    {
        /** @var \App\Models\RatingScore $rating */
        $rating = optional($orderProduct->rating);

        return array_merge([
            'rating' => $rating->value,
            'rating_comment' => $rating->comment,
        ], parent::resource($orderProduct));
    }
}
