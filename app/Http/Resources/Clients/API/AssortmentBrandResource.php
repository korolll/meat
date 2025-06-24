<?php

namespace App\Http\Resources\Clients\API;

use App\Models\AssortmentBrand;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class AssortmentBrandResource extends JsonResource
{
    /**
     * @param AssortmentBrand $assortmentBrand
     * @return array
     */
    public function resource($assortmentBrand)
    {
        return [
            'uuid' => $assortmentBrand->uuid,
            'name' => $assortmentBrand->name,
            'created_at' => $assortmentBrand->created_at,
        ];
    }
}
