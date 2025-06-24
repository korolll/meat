<?php

namespace App\Http\Resources\Clients\API;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class StoryResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'storyTabs' => function (Relation $query) {
                return $query->select();
            },
            'logoFile' => function (Relation $query) {
                return $query->select('uuid', 'path');
            }
        ]);
    }

    /**
     * @param \App\Models\Story $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'id' => $resource->id,
            'name'=> $resource->name,
            'logo_file_path' => Storage::url($resource->logoFile->path),
            'tabs' => StoryTabResource::collection($resource->storyTabs),
            'created_at'=> $resource->created_at
        ];
    }
}
