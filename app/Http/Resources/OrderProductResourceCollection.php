<?php

namespace App\Http\Resources;

use App\Http\Resources\Clients\API\AssortmentSimpleResource;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrderProductResourceCollection extends JsonResource
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
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        $resource->loadMissing([
            $prefix . 'product' => function (Relation $query) {
                return $query->select([
                    'uuid',
                    'assortment_uuid'
                ]);
            },
            $prefix . 'product.assortment',
        ]);

        AssortmentSimpleResource::loadMissing($resource, $prefix . 'product.assortment.');
    }

    /**
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return array
     */
    public function resource($orderProduct)
    {
        if ($orderProduct->price_with_discount) {
            $price = $orderProduct->price_with_discount;
            $originalPrice = $orderProduct->price_with_discount + $orderProduct->discount;
            $totalDiscount = $orderProduct->discount * $orderProduct->quantity;
        } else {
            $price = $orderProduct->total_amount_with_discount / $orderProduct->quantity;
            $originalPrice = null;
            $totalDiscount = null;
        }

       if ($orderProduct->discountable_type) {
            $info = $this->descriptionResolver->resolve($orderProduct->discountable_type);
            if ($info) {
                $orderProduct->discount_type_color = $info->color;
                $orderProduct->discount_type_name = $info->name;
            }
        }
        return [
            'uuid' => $orderProduct->uuid,

            'order_uuid' => $orderProduct->order_uuid,
            'product_uuid' => $orderProduct->product_uuid,
            'assortment' => AssortmentSimpleResource::make($orderProduct->product->assortment),

            'quantity' => $orderProduct->quantity,
            'total_weight' => $orderProduct->total_weight,

            'price_with_discount' => $orderProduct->price_with_discount,
            'total_amount_with_discount' => $orderProduct->total_amount_with_discount,

            'price' => $price,
            'original_price' => $originalPrice,
            'discount' => $orderProduct->discount,
            'total_discount' => $totalDiscount,

            'paid_bonus' => $orderProduct->paid_bonus,
            'total_bonus' => $orderProduct->total_bonus,

            'discountable_type' => $orderProduct->discountable_type,
            'discount_type_color' => $orderProduct->discount_type_color,
            'discount_type_name' => $orderProduct->discount_type_name,

            'created_at' => $orderProduct->created_at,
            'updated_at' => $orderProduct->updated_at,
        ];
    }
}
