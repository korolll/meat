<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Http\Resources\FileShortInfoResource;
use App\Models\Assortment;
use App\Models\RatingScore;
use App\Models\ReceiptLine;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReceiptLineResource extends JsonResource
{
    /**
     * @var \App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected PromoDescriptionResolverInterface $descriptionResolver;

    /**
     * @param $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->descriptionResolver = app(PromoDescriptionResolverInterface::class);
    }

    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'assortment_unit_id', 'weight');
            },
            'assortment.rating' => function (Relation $query) {
                return $query->select('reference_type', 'reference_id', 'value');
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
        if ($receiptLine->price_with_discount) {
            $price = $receiptLine->price_with_discount;
            $originalPrice = $receiptLine->price_with_discount + $receiptLine->discount;
            $totalDiscount = $receiptLine->discount * $receiptLine->quantity;
        } else {
            $price = $receiptLine->total / $receiptLine->quantity;
            $originalPrice = null;
            $totalDiscount = null;
        }

        if ($receiptLine->discountable_type) {
            $info = $this->descriptionResolver->resolve($receiptLine->discountable_type);
            if ($info) {
                $receiptLine->discount_type_color = $info->color;
                $receiptLine->discount_type_name = $info->name;
            }
        }

        /** @var Assortment $assortment */
        $assortment = optional($receiptLine->assortment);

        /** @var RatingScore $rating */
        $rating = optional($receiptLine->rating);

        return [
            'uuid' => $receiptLine->uuid,
            'assortment_uuid' => $assortment->uuid,
            'assortment_name' => $assortment->name,
            'assortment_rating' => optional($assortment->rating)->value,
            'assortment_unit_id' => $assortment->assortment_unit_id,
            'assortment_weight' => $assortment->weight,
            'quantity' => $receiptLine->quantity,
            'total' => $receiptLine->total,
            'discountable_type' => $receiptLine->discountable_type,
            'discount_type_color' => $receiptLine->discount_type_color,
            'discount_type_name' => $receiptLine->discount_type_name,
            'price' => $price,

            'paid_bonus' => $receiptLine->paid_bonus,
            'total_bonus' => $receiptLine->total_bonus,

            'original_price' => $originalPrice,
            'discount' => $receiptLine->discount,
            'total_discount' => $totalDiscount,
            'rating' => $rating->value,
            'rating_comment' => $rating->comment,
            'assortment_images' => FileShortInfoResource::collection($assortment->images ?: []),
        ];
    }
}
