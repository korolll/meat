<?php

namespace App\Contracts\Models\Product;

use Illuminate\Database\Query\Builder;


interface MakeProductsAvailableForRequestQueryContract
{
    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Builder|Builder
     */
    public function make(array $attributes = []);
}
