<?php

namespace App\Services\Management\Product\Contracts;

use App\Models\Catalog;
use App\Models\Product;
use App\Models\User;

interface ProductReplicatorContract
{
    /**
     * @param Product $product
     * @param User $recipient
     * @param Catalog $catalog
     * @param array $attributes
     * @return Product
     */
    public function replicate(Product $product, User $recipient, Catalog $catalog, array $attributes = []);
}
