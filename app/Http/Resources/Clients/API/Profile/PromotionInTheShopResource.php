<?php


namespace App\Http\Resources\Clients\API\Profile;


use App\Models\PromotionInTheShopAssortment;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Management\Client\Product\CalculateContext;
use App\Services\Management\Client\Product\Discount\Concrete\InTheShopDiscountResolver;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use App\Services\Management\Client\Product\TargetEnum;
use App\Services\Money\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Resources\FileShortInfoResource;
use Illuminate\Support\Arr;

class PromotionInTheShopResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'shop' => function (Relation $query) {
                return $query->select('uuid', 'full_name');
            },
            'client',
            'promotionInTheShopAssortments',
            'promotionInTheShopAssortments.assortment',
        ]);
    }

    /**
     * @param \App\Models\ClientPromotion $resource
     *
     * @return array
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function resource($resource): array
    {
        $assortmentUuids = [];
        $map = [];

        foreach ($resource->promotionInTheShopAssortments as $inTheShopAssortment) {
            $assortmentUuids[] = $inTheShopAssortment->assortment_uuid;
            $map[$inTheShopAssortment->assortment_uuid] = $inTheShopAssortment;
        }

        $products = $resource->shop
            ->products()
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->get();

        $products->loadMissing('assortment');

        /** @var InTheShopDiscountResolver $calculator */
        $discountResolver = app(InTheShopDiscountResolver::class);
        $client = $resource->client;
        $ctx = new CalculateContext(
            $client,
            TargetEnum::ORDER
        );

        $discountResolver->preLoad($ctx, $products->all());
        $cardData = $client->getShoppingCart()->getData();

        /** @var PromoDescriptionResolverInterface $descriptionResolver */
        $descriptionResolver = app(PromoDescriptionResolverInterface::class);
        /** @var \App\Models\Product $product */
        foreach ($products as $product) {
            $discount = $discountResolver->resolve($ctx, $product);
            /** @var PromotionInTheShopAssortment $model */
            $model = $map[$product->assortment_uuid];
            $model->price = $product->price;
            $model->quantity = $product->quantity;

            if ($discount) {
                $model->price_with_discount = $discount->getPrice();
                $model->discount_type = $discount->getDiscountModel()->getMorphClass();
                $info = $descriptionResolver->resolve($model->discount_type);
                if ($info) {
                    $model->discount_type_color = $info->color;
                    $model->discount_type_name = $info->name;
                }
            }
        }

        $discountResolver->clearPreloadedData();

        return [
            'uuid' => $resource->uuid,
            'store_uuid' => $resource->shop->uuid,
            'store_name' => $resource->shop->full_name,
            'discount_percent' => $resource->discount_percent,
            'products' => $resource->promotionInTheShopAssortments->map(function (PromotionInTheShopAssortment $promotion) use ($cardData) {
                $totalBonus = null;
                $assortment = $promotion->assortment;

                $targetPrice = $promotion->price_with_discount ?: $promotion->price;
                if ($targetPrice) {
                    if ($assortment->bonus_percent) {
                        $totalBonus = MoneyHelper::of($targetPrice)
                            ->multipliedBy($assortment->bonus_percent)
                            ->dividedBy(100);
                        $totalBonus = MoneyHelper::toBonus($totalBonus);
                    } else {
                        $totalBonus = 0;
                    }
                }

                return [
                    'uuid' => $promotion->assortment_uuid,
                    'name' => $assortment->name,
                    'rating' => $assortment->rating,
                    'weight' => $assortment->weight,
                    'assortment_unit_id' => $assortment->assortment_unit_id,
                    'quantity_in_client_cart' => (float)Arr::get($cardData, $promotion->assortment_uuid . '.quantity', 0),
                    'total_bonus' => $totalBonus,

                    'images' => FileShortInfoResource::collection($assortment->images),

                    'price' => $promotion->price,
                    'price_with_discount' => $promotion->price_with_discount,
                    'quantity' => $promotion->quantity,
                    'discount_type' => $promotion->discount_type,
                    'discount_type_color' => $promotion->discount_type_color,
                    'discount_type_name' => $promotion->discount_type_name,
                ];
            }),
        ];
    }
}
