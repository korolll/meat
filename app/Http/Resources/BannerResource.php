<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class BannerResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'logoFile' => function (Relation $query) {
                return $query->select('uuid', 'path');
            }
        ]);
    }

    /**
     * @param \App\Models\Banner $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'description' => $resource->description,
            'number' => $resource->number,
            'enabled' => $resource->enabled,
            'logo_file' => FileShortInfoResource::make($resource->logoFile),
            'created_at' => $resource->created_at,
            'reference_type' => $resource->reference_type,
            'reference_uuid' => $resource->reference_uuid
        ];
    }
}
