<?php

namespace App\Http\Resources\Clients\API;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

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
            },
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
            'title' => $resource->title,
            'text' => $resource->text,
            'text_color' => $resource->text_color,
            'duration' => $resource->duration,
            'button_title' => $resource->button_title,
            'url' => $resource->url,
            'logo_file_path' => Storage::url($resource->logoFile->path),
            'created_at' => $resource->created_at,
        ];
    }
}
