<?php

namespace App\Services\Management\Product;

use App\Contracts\Management\Product\ByAssortmentProductMakerContract;
use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\User;
use App\Services\Storaging\Catalog\Contracts\DefaultCatalogFinderContract;

class ByAssortmentProductMaker implements ByAssortmentProductMakerContract
{
    /**
     * @var DefaultCatalogFinderContract
     */
    protected $catalogFinder;

    /**
     * AssortmentMatrixRowToProductConverter constructor.
     * @param DefaultCatalogFinderContract $catalogFinder
     */
    public function __construct(DefaultCatalogFinderContract $catalogFinder)
    {
        $this->catalogFinder = $catalogFinder;
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @param array $attributes
     * @return Product
     */
    public function make(User $user, Assortment $assortment, array $attributes = []): Product
    {
        if (($product = $this->maybeProductAlreadyExists($user, $assortment)) !== null) {
            return $product;
        }

        return $this->makeProduct(
            $user,
            $assortment,
            $this->catalogFinder->find($user),
            $attributes
        );
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @return Product|null
     */
    protected function maybeProductAlreadyExists(User $user, Assortment $assortment): ?Product
    {
        return $user->products()
            ->where('assortment_uuid', $assortment->uuid)
            ->first();
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @param Catalog $catalog
     * @param array $attributes
     * @return Product
     */
    protected function makeProduct(User $user, Assortment $assortment, Catalog $catalog, array $attributes = []): Product
    {
        $product = new Product();

        $product->forceFill($attributes);
        $product->catalog()->associate($catalog);
        $product->user()->associate($user);
        $product->assortment()->associate($assortment);
        $product->save();

        return $product;
    }
}
