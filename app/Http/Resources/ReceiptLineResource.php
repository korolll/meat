<?php

namespace App\Http\Resources;

use App\Models\Assortment;
use App\Models\RatingScore;
use App\Models\ReceiptLine;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReceiptLineResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'rating' => function (Relation $query) {
                return $query->select([
                    'uuid',
                    'rated_through_reference_type',
                    'rated_through_reference_id',
                    'value',
                    'additional_attributes',
                ]);
            },
            'assortment.images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            }
        ]);
    }

    /**
     * @param ReceiptLine $receiptLine
     * @return array
     */
    public function resource($receiptLine)
    {
        /** @var Assortment $assortment */
        $assortment = optional($receiptLine->assortment);

        /** @var RatingScore $rating */
        $rating = optional($receiptLine->rating);

        return [
            'uuid' => $receiptLine->uuid,
            'assortment_uuid' => $assortment->uuid,
            'assortment_name' => $assortment->name,
            'quantity' => $receiptLine->quantity,
            'total' => $receiptLine->total,
            'discountable_type' => $receiptLine->discountable_type,
            'rating' => $rating->value,
            'rating_comment' => $rating->comment,
            'assortment_images' => FileShortInfoResource::collection($receiptLine->assortment->images ?: []),

            'paid_bonus' => $receiptLine->paid_bonus,
            'total_bonus' => $receiptLine->total_bonus,
        ];
    }
}
