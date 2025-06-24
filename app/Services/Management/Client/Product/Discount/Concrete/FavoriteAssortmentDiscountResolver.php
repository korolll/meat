<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Client;
use App\Models\ClientActivePromoFavoriteAssortment;
use App\Models\Product;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolverWithCache;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Models\Assortment\BannedAssortmentCheckerInterface;
use App\Services\Money\MoneyHelper;
use Brick\Money\Exception\MoneyMismatchException;
use Carbon\CarbonInterface;

class FavoriteAssortmentDiscountResolver extends AbstractClientProductDiscountResolverWithCache
{
    public function __construct(BannedAssortmentCheckerInterface $bannedAssortmentChecker)
    {
        $this->checkBannedAssortments = true;
        parent::__construct($bannedAssortmentChecker);
    }

    /**
     * @param CalculateContextInterface $ctx
     * @param iterable<Product> $products
     *
     * @return array<string, DiscountData>
     * @throws MoneyMismatchException
     */
    protected function findDiscountsValidProducts(CalculateContextInterface $ctx, iterable $products): array
    {
        $assortmentUuids = [];
        foreach ($products as $product) {
            $assortmentUuid = $product->assortment_uuid;
            $assortmentUuids[$assortmentUuid] = $assortmentUuid;
        }

        $priceModels = ClientActivePromoFavoriteAssortment::activeAt($ctx->getMoment())
            ->where('client_uuid', $ctx->getClient()->uuid)
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->get()
            ->keyBy('assortment_uuid');

        $result = [];
        foreach ($products as $product) {
            $assortmentUuid = $product->assortment_uuid;
            if (!$priceModels->has($assortmentUuid)) {
                $result[$product->uuid] = null;
                continue;
            }

            /** @var ClientActivePromoFavoriteAssortment $priceModel */
            $priceModel = $priceModels[$assortmentUuid];
            $newPrice = MoneyHelper::valueWithDiscount($priceModel->discount_percent, $product->price);
            $newPrice = MoneyHelper::toFloat($newPrice);
            $result[$product->uuid] = new DiscountData($newPrice, $priceModel);
        }

        return $result;
    }
}
