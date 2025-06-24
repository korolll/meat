<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Client;
use App\Models\Product;
use App\Models\PromoYellowPrice;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolverWithCache;
use App\Services\Management\Client\Product\Discount\DiscountData;
use Brick\Money\Exception\MoneyMismatchException;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\JoinClause;

class YellowPriceDiscountResolver extends AbstractClientProductDiscountResolverWithCache
{
    /**
     * @param CalculateContextInterface $ctx
     * @param iterable<Product> $products
     *
     * @return array<string, DiscountData>
     * @throws MoneyMismatchException
     */
    protected function findDiscountsValidProducts(CalculateContextInterface $ctx, iterable $products): array
    {
        $userUuids = [];
        $assortmentUuids = [];
        foreach ($products as $product) {
            $userUuid = $product->user_uuid;
            $assortmentUuid = $product->assortment_uuid;

            $userUuids[$userUuid] = $userUuid;
            $assortmentUuids[$assortmentUuid] = $assortmentUuid;
        }

        $priceModels = PromoYellowPrice::activeAt($ctx->getMoment())
            ->enabled()
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->join('promo_yellow_price_user', function (JoinClause $join) use ($userUuids) {
                $join->on('promo_yellow_price_user.promo_yellow_price_uuid', 'promo_yellow_prices.uuid');
                $join->whereIn('promo_yellow_price_user.user_uuid', $userUuids);
            })
            ->get([
                'promo_yellow_prices.*',
                'promo_yellow_price_user.user_uuid'
            ])
            ->keyBy(function (PromoYellowPrice $promotion, $key) {
                return $promotion->user_uuid . $promotion->assortment_uuid;
            });

        $result = [];
        foreach ($products as $product) {
            $uuid = $product->uuid;
            $userUuid = $product->user_uuid;
            $assortmentUuid = $product->assortment_uuid;

            $searchKey = $userUuid . $assortmentUuid;
            if (! $priceModels->has($searchKey)) {
                $result[$uuid] = null;
            } else {
                /** @var PromoYellowPrice $priceModel */
                $priceModel = $priceModels[$searchKey];
                $result[$uuid] = new DiscountData($priceModel->price, $priceModel);
            }
        }

        return $result;
    }
}
