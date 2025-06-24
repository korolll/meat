<?php

namespace App\Contracts\Management\Product;

use App\Models\Assortment;
use App\Models\Product;
use App\Models\User;

interface ByAssortmentProductMakerContract
{
    /**
     * @param User $user
     * @param Assortment $assortment
     * @param array $attributes
     * @return Product
     */
    public function make(User $user, Assortment $assortment, array $attributes = []): Product;
}
