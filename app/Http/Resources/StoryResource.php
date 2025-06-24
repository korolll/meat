<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class StoryResource extends JsonResource
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
     * @param \App\Models\Story $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'id' => $resource->id,
            'story_name' => $resource->story_name,
            'name' => $resource->name,
            'show_from' => $resource->show_from,
            'show_to' => $resource->show_to,
            'logo_file' => FileShortInfoResource::make($resource->logoFile),
            'created_at' => $resource->created_at
        ];
    }
}
