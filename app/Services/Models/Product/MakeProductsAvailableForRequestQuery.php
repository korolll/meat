<?php

namespace App\Services\Models\Product;

use App\Contracts\Models\Product\MakeProductsAvailableForRequestQueryContract;
use App\Models\PriceList;
use App\Models\Product;
use App\Services\Database\VirtualColumns\ProductPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MakeProductsAvailableForRequestQuery implements MakeProductsAvailableForRequestQueryContract
{
    /**
     * @param array $attributes
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function make(array $attributes = [])
    {
        $customerUserUuid = Arr::get($attributes, 'customer_user_uuid');
        $assortmentUuids = Arr::get($attributes, 'assortment_uuids', []);
        $productUserUuids = Arr::get($attributes, 'product_user_uuids', []);

        $query = Product::query()
            ->select(['products.*'])
            ->addVirtualColumn(ProductPrice::class, 'price', [$customerUserUuid]);

        $query->isActive();
        $query->ownedByProductSellers();
        $query->whereNotNull('price_list_product.price_new');

        if ($assortmentUuids) {
            $query->whereIn('products.assortment_uuid', $assortmentUuids);
        }

        if ($productUserUuids) {
            $query->whereIn('products.user_uuid', $productUserUuids);
        }

        return Product::query()->fromSub($query->toBase(), 'products');
    }
}
