<?php

namespace App\Http\Resources\Clients\API;

use App\Http\Resources\FileShortInfoResource;
use App\Models\Assortment;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class AssortmentSimpleResource extends JsonResource
{
    /**
     * @param mixed  $resource
     * @param string $prefix
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        $resource->loadMissing([
            $prefix . 'rating' => function (Relation $query) {
                return $query->select('reference_type', 'reference_id', 'value');
            },
            $prefix . 'catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            $prefix . 'images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            },
        ]);
    }

    /**
     * @param Assortment $assortment
     *
     * @return array
     */
    public function resource($assortment)
    {
        return [
            'uuid' => $assortment->uuid,
            'name' => $assortment->name,
            'assortment_unit_id' => $assortment->assortment_unit_id,
            'assortment_weight' => $assortment->weight,

            'catalog_uuid' => $assortment->catalog->uuid,
            'catalog_name' => $assortment->catalog->name,

            'rating' => optional($assortment->rating)->value,
            'images' => FileShortInfoResource::collection($assortment->images),
        ];
    }
}
