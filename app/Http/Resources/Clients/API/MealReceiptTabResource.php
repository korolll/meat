<?php

namespace App\Http\Resources\Clients\API;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class MealReceiptTabResource extends JsonResource
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
        ]);
    }

    /**
     * @param \App\Models\MealReceiptTab $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'uuid' => $resource->uuid,
            'title' => $resource->title,
            'text' => $resource->text,
            'text_color' => $resource->text_color,
            'duration' => $resource->duration,
            'sequence' => $resource->sequence,
            'button_title' => $resource->button_title,

            'url' => $resource->url,
            'file_path' => Storage::url($resource->file->path),

            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
