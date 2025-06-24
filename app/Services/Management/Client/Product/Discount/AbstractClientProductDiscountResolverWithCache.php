<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\Client;
use App\Models\Product;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Models\Assortment\BannedAssortmentCheckerInterface;
use Carbon\CarbonInterface;

abstract class AbstractClientProductDiscountResolverWithCache extends AbstractClientProductDiscountResolver implements ClientProductDiscountResolverPreloadInterface
{
    protected array $preloadedData = [];

    protected bool $checkBannedAssortments = false;

    protected BannedAssortmentCheckerInterface $bannedAssortmentChecker;

    public function __construct(BannedAssortmentCheckerInterface $bannedAssortmentChecker)
    {
        $this->bannedAssortmentChecker = $bannedAssortmentChecker;
    }

    public function resolve(CalculateContextInterface $ctx, Product $product): ?DiscountDataInterface
    {
        $productUuid = $product->uuid;
        if (array_key_exists($productUuid, $this->preloadedData)) {
            return $this->preloadedData[$productUuid];
        }

        $discounts = $this->findDiscounts($ctx, [$product]);
        return $discounts[$productUuid];
    }

    public function preLoad(CalculateContextInterface $ctx, iterable $products): void
    {
        $this->preloadedData = $this->findDiscounts($ctx, $products);
    }

    public function clearPreloadedData(): void
    {
        $this->preloadedData = [];
    }

    /**
     * @param CalculateContextInterface $ctx
     * @param iterable $products
     *
     * @return array
     */
    protected function findDiscounts(CalculateContextInterface $ctx, iterable $products): array
    {
        $result = [];
        $preFiltered = [];
        $assortments = [];
        foreach ($products as $product) {
            if (! $this->isProductValid($product)) {
                $result[$product->uuid] = null;
            } else {
                if ($this->checkBannedAssortments) {
                    $assortments[$product->assortment_uuid] = $product->assortment;
                }

                $preFiltered[] = $product;
            }
        }

        $filtered = [];
        if ($assortments && $this->checkBannedAssortments) {
            $bannedAssortments = $this->bannedAssortmentChecker->checkCollection($assortments);
            foreach ($preFiltered as $product) {
                if ($bannedAssortments[$product->assortment_uuid]) {
                    $result[$product->uuid] = null;
                } else {
                    $filtered[] = $product;
                }
            }
        } else {
            $filtered = $preFiltered;
        }

        if ($filtered) {
            $result += $this->findDiscountsValidProducts($ctx, $filtered);
        }

        return $result;
    }

    /**
     * @param iterable<Product> $products
     *
     * @return array<string, null>
     */
    protected function makeNullDiscountMap(iterable $products)
    {
        $result = [];
        foreach ($products as $product) {
            $result[$product->uuid] = null;
        }

        return $result;
    }

    protected abstract function findDiscountsValidProducts(CalculateContextInterface $ctx, iterable $products): array;
}
