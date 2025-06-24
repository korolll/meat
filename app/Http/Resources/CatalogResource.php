<?php

namespace App\Http\Resources;

use App\Models\Catalog;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class CatalogResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        $resource->loadMissing([
            $prefix . 'parent' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            $prefix . 'image' => function (Relation $query) {
                return $query->select('*');
            },
        ]);
    }

    /**
     * @param Catalog $catalog
     * @return array
     */
    public function resource($catalog)
    {
        $parent = optional($catalog->parent);

        return [
            'uuid' => $catalog->uuid,
            'catalog_uuid' => $parent->uuid,
            'catalog_name' => $parent->name,
            'name' => $catalog->name,
            'created_at' => $catalog->created_at,
            'assortments_count' => $catalog->assortments_count,
            'products_count' => $catalog->products_count,
            'image' => FileShortInfoResource::make($catalog->image),
            'sort_number' => $catalog->sort_number
        ];
    }
}
