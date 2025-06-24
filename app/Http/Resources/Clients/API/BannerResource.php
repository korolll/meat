<?php

namespace App\Http\Resources\Clients\API;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

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
            'name'=> $resource->name,
            'logo_file_path' => Storage::url($resource->logoFile->path),
            'description' => $resource->dscription,
            'number' => $resource->number,
            'enabled' => $resource->enabled,
            'created_at'=> $resource->created_at,
            'reference_type' => $resource->reference_type,
            'reference_uuid' => $resource->reference_uuid
        ];
    }
}
