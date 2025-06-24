<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Client;
use App\Models\ClientPromotion;
use App\Models\Product;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolverWithCache;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Money\MoneyHelper;
use Brick\Money\Exception\MoneyMismatchException;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\JoinClause;

class InTheShopDiscountResolver extends AbstractClientProductDiscountResolverWithCache
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

        $priceModels = ClientPromotion::activeAt($ctx->getMoment())
            ->where('client_uuid', $ctx->getClient()->uuid)
            ->whereIn('user_uuid', $userUuids)
            ->join('promotion_in_the_shop_assortments as pa', function (JoinClause $clause) use ($assortmentUuids) {
                $clause->on('pa.client_promotion_uuid', '=', 'client_promotions.uuid');
                $clause->whereIn('pa.assortment_uuid', $assortmentUuids);
            })
            ->get([
                'client_promotions.*',
                'pa.assortment_uuid'
            ])
            ->keyBy(function (ClientPromotion $promotion, $key) {
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
                /** @var ClientPromotion $priceModel */
                $priceModel = $priceModels[$searchKey];
                $newPrice = MoneyHelper::valueWithDiscount($priceModel->discount_percent, $product->price);
                $newPrice = MoneyHelper::toFloat($newPrice);
                $result[$uuid] = new DiscountData($newPrice, $priceModel);
            }
        }

        return $result;
    }
}
