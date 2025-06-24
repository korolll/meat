<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\Client;
use App\Models\Product;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Models\Assortment\BannedAssortmentCheckerInterface;
use Carbon\CarbonInterface;

class ClientProductCollectionDiscountResolver extends AbstractClientProductDiscountResolver implements ClientProductDiscountResolverPreloadInterface
{
    /**
     * @var array<ClientProductDiscountResolverInterface>
     */
    private array $resolvers;

    /**
     * @var \App\Services\Models\Assortment\BannedAssortmentCheckerInterface
     */
    private BannedAssortmentCheckerInterface $assortmentChecker;

    /**
     * @param array<ClientProductDiscountResolverInterface> $resolvers
     *
     * @throws \Exception
     */
    public function __construct(array $resolvers, BannedAssortmentCheckerInterface $assortmentChecker)
    {
        foreach ($resolvers as $resolver) {
            if (! $resolver instanceof ClientProductDiscountResolverInterface) {
                throw new \Exception('Each resolver should implement ClientProductDiscountResolverInterface');
            }
        }

        $this->resolvers = $resolvers;
        $this->assortmentChecker = $assortmentChecker;
    }

    /**
     * @param CalculateContextInterface $ctx
     * @param Product $product
     *
     * @return DiscountDataInterface|null
     */
    public function resolve(CalculateContextInterface $ctx, Product $product): ?DiscountDataInterface
    {
        if (! $this->isProductValid($product)) {
            return null;
        }

        /** @var ?DiscountDataInterface $bestVariant */
        $bestVariant = null;
        foreach ($this->resolvers as $resolver) {
            $resolved = $resolver->resolve($ctx, $product);
            if (! $resolved) {
                continue;
            }

            if ($bestVariant === null || $bestVariant->getPrice() > $resolved->getPrice()) {
                $bestVariant = $resolved;
                if ($resolved->isHighPriority()) {
                    break;
                }
            }
        }

        return $bestVariant;
    }

    public function preLoad(CalculateContextInterface $ctx, iterable $products): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof ClientProductDiscountResolverPreloadInterface) {
                $resolver->preLoad($ctx, $products);
            }
        }
    }

    public function clearPreloadedData(): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof ClientProductDiscountResolverPreloadInterface) {
                $resolver->clearPreloadedData();
            }
        }

        $this->assortmentChecker->clearCache();
    }
}
