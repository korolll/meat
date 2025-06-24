<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class StoryTabResource extends JsonResource
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
     * @param \App\Models\StoryTab $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'id' => $resource->id,
            'story_id' => $resource->story_id,
            'title' => $resource->title,
            'text' => $resource->text,
            'text_color' => $resource->text_color,
            'duration' => $resource->duration,
            'button_title' => $resource->button_title,
            'url' => $resource->url,
            'logo_file' => FileShortInfoResource::make($resource->logoFile),
            'created_at' => $resource->created_at,
        ];
    }
}
