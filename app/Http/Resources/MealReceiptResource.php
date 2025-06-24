<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class MealReceiptResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'file' => function (Relation $query) {
                return $query->select('uuid', 'path');
            },
            'assortments' => function (Relation $query) {
                return $query->select('uuid');
            }
        ]);
    }

    /**
     * @param \App\Models\MealReceipt $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'uuid' => $resource->uuid,
            'name' => $resource->name,
            'section' => $resource->section,
            'title' => $resource->title,
            'description' => $resource->description,
            'ingredients' => $resource->ingredients,
            'duration' => $resource->duration,
            'file' => FileShortInfoResource::make($resource->file),
            'assortment_uuids' => $resource->assortments->pluck('uuid'),
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
