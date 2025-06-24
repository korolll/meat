<?php

namespace App\Http\Resources;

use App\Http\Resources\Clients\API\AssortmentSimpleResource;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use Illuminate\Database\Eloquent\Relations\Relation;

class PurchaseLineResource extends JsonResource
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
     * @param mixed  $resource
     * @param string $prefix
     *
     * @return void
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        $resource->loadMissing([
            $prefix . 'product' => function (Relation $query) {
                return $query->select('uuid', 'assortment_uuid');
            },
            $prefix . 'product.assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'assortment_unit_id', 'weight', 'catalog_uuid');
            },
            $prefix . 'ratingReceipt' => function (Relation $query) {
                return $query->select('*');
            },
            $prefix . 'ratingOrder' => function (Relation $query) {
                return $query->select('*');
            }
        ]);

        AssortmentSimpleResource::loadMissing($resource, $prefix . 'product.assortment.');
    }

    /**
     * @param \App\Models\PurchaseView $purchase
     *
     * @return array
     */
    public function resource($purchase)
    {
        if ($purchase->discountable_type) {
            $info = $this->descriptionResolver->resolve($purchase->discountable_type);
            if ($info) {
                $purchase->discount_type_color = $info->color;
                $purchase->discount_type_name = $info->name;
            }
        }

        $price = $purchase->price_with_discount;
        $originalPrice = $price + $purchase->discount;
        if ($purchase->total_discount) {
            $totalDiscount = $purchase->total_discount;
        } else {
            $totalDiscount = $purchase->discount * $purchase->quantity;
        }

        $assortment = null;
        if ($purchase->product) {
            $assortment = $purchase->product->assortment;
        }

        if ($purchase->total_weight) {
            $totalWeight = $purchase->total_weight;
        } elseif ($assortment) {
            $totalWeight = $assortment->weight * $purchase->quantity;
        } else {
            $totalWeight = null;
        }

        if ($purchase->ratingOrder) {
            $rating = $purchase->ratingOrder;
        } elseif ($purchase->ratingReceipt) {
            $rating = $purchase->ratingReceipt;
        } else {
            $rating = optional();
        }

        return [
            'source' => $purchase->source,
            'source_id' => $purchase->source_id,

            'source_line' => $purchase->source_line,
            'source_line_id' => $purchase->source_line_id,

            'product_uuid' => $purchase->product_uuid,
            'assortment' => $assortment ? AssortmentSimpleResource::make($assortment) : null,

            'quantity' => $purchase->quantity,
            'total_weight' => $totalWeight,

            'price_with_discount' => $price,
            'discount' => $purchase->discount,

            'original_price' => $originalPrice,
            'total_discount' => $totalDiscount,

            'total_bonus' => $purchase->total_bonus,
            'paid_bonus' => $purchase->paid_bonus,

            'discountable_type' => $purchase->discountable_type,
            'discount_type_color' => $purchase->discount_type_color,
            'discount_type_name' => $purchase->discount_type_name,

            'rating' => $rating->value,
            'rating_comment' => $rating->comment,

            'created_at' => $purchase->created_at,
        ];
    }
}
