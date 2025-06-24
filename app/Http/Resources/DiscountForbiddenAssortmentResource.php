<?php

namespace App\Http\Resources;

use App\Models\DiscountForbiddenAssortment;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class DiscountForbiddenAssortmentResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment'
        ]);

        AssortmentResource::loadMissing($resource, 'assortment.');
    }

    /**
     * @param DiscountForbiddenAssortment $resource
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,
            'assortment_uuid' => $resource->assortment_uuid,
            'assortment_name' => $resource->assortment->name,
            'created_at' => $resource->created_at,

            'assortment' => AssortmentResource::make($resource->assortment),
        ];
    }
}
