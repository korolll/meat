<?php

namespace App\Http\Resources;

use App\Models\DiscountForbiddenCatalog;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class DiscountForbiddenCatalogResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'catalog'
        ]);

        CatalogResource::loadMissing($resource, 'catalog.');
    }

    /**
     * @param DiscountForbiddenCatalog $resource
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,
            'catalog_uuid' => $resource->catalog_uuid,
            'created_at' => $resource->created_at,

            'catalog' => CatalogResource::make($resource->catalog),
        ];
    }
}
