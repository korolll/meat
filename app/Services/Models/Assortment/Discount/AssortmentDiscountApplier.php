<?php

namespace App\Services\Models\Assortment\Discount;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\User;
use App\Services\Management\Client\Product\CalculateContext;
use App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface;
use App\Services\Management\Client\Product\Discount\DiscountModelInterface;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use App\Services\Management\Client\Product\TargetEnum;
use Illuminate\Support\Arr;

class AssortmentDiscountApplier implements AssortmentDiscountApplierInterface
{
    /**
     * @var \App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface
     */
    private ClientProductDiscountResolverPreloadInterface $discountResolver;
    /**
     * @var \App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface
     */
    private PromoDescriptionResolverInterface $descriptionResolver;

    /**
     * @param \App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface $discountResolver
     * @param \App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface      $descriptionResolver
     */
    public function __construct(ClientProductDiscountResolverPreloadInterface $discountResolver, PromoDescriptionResolverInterface $descriptionResolver)
    {
        $this->discountResolver = $discountResolver;
        $this->descriptionResolver = $descriptionResolver;
    }

    /**
     * @inerhitDoc
     */
    public function apply(
        User $store,
        Client $client,
        array $assortmentsMapByUuid,
        bool $addCurrentPrice = false,
        bool $fakePromoDiverseFoodClientDiscount = false,
        TargetEnum $targetEnum = TargetEnum::ORDER
    ): void
    {
        $products = $store->products()->whereIn('assortment_uuid', array_keys($assortmentsMapByUuid))->get();
        $products->loadMissing('assortment');
        $ctx = new CalculateContext(
            $client,
            $targetEnum
        );
        $this->discountResolver->preLoad($ctx, $products);

        /** @var \App\Models\Product $product */
        foreach ($products as $product) {
            // For relations support
            $assortments = Arr::wrap($assortmentsMapByUuid[$product->assortment_uuid]);
            $discount = $this->discountResolver->resolve($ctx, $product);
            $fakePrice = null;

            if ($discount) {
                $discountPrice = $discount->getPrice();
                $discountModel = $discount->getDiscountModel();
                $discountType = $discountModel->getMorphClass();
                if ($fakePromoDiverseFoodClientDiscount && $discountModel instanceof PromoDiverseFoodClientDiscount) {
                    $fakePrice = $discountPrice;
                    $info = null;
                } else {
                    $info = $this->descriptionResolver->resolve($discountType);
                }

                foreach ($assortments as $assortment) {
                    if ($fakePrice) {
                        $assortment->current_price = $fakePrice;
                        continue;
                    }

                    if ($addCurrentPrice) {
                        $assortment->current_price = $product->price;
                    }

                    $assortment->price_with_discount = $discountPrice;
                    $assortment->discount_type = $discountType;
                    $assortment->discount_model = $discountModel;
                    if ($info) {
                        $assortment->discount_type_color = $info->color;
                        $assortment->discount_type_name = $info->name;
                    }

                    if ($discountModel instanceof DiscountModelInterface) {
                        $assortment->discount_active_from = $discountModel->getActiveFrom();
                        $assortment->discount_active_to = $discountModel->getActiveTo();
                    }
                }
            } elseif ($addCurrentPrice) {
                foreach ($assortments as $assortment) {
                    $assortment->current_price = $product->price;
                }
            }
        }

        $this->discountResolver->clearPreloadedData();
    }
}
