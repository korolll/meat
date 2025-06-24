<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Client;
use App\Models\Product;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolverWithCache;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Models\Assortment\BannedAssortmentCheckerInterface;
use App\Services\Money\MoneyHelper;
use Brick\Money\Exception\MoneyMismatchException;
use Carbon\CarbonInterface;

class DiverseFoodPriceDiscountResolver extends AbstractClientProductDiscountResolverWithCache
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
        /** @var PromoDiverseFoodClientDiscount $priceModel */
        $priceModel = PromoDiverseFoodClientDiscount::activeAt($ctx->getMoment())
            ->where('client_uuid', $ctx->getClient()->uuid)
            ->first();

        $result = [];
        if (!$priceModel) {
            return $this->makeNullDiscountMap($products);
        }

        foreach ($products as $product) {
            $newPrice = MoneyHelper::valueWithDiscount($priceModel->discount_percent, $product->price);
            $newPrice = MoneyHelper::toFloat($newPrice);
            $result[$product->uuid] = new DiscountData($newPrice, $priceModel);
        }

        return $result;
    }
}
