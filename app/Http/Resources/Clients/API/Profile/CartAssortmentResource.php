<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Http\Resources\Clients\API\AssortmentResource;
use App\Models\Assortment;

class CartAssortmentResource extends AssortmentResource
{
    /**
     * @param Assortment $assortment
     *
     * @return array
     */
    public function resource($assortment)
    {
        $parent = parent::resource($assortment);
        return [
            'assortment' => $parent,
            'quantity' => $assortment->shopping_cart_quantity
        ];
    }
}
