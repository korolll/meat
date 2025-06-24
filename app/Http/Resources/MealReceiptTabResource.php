<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

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
            }
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
            'meal_receipt_uuid' => $resource->meal_receipt_uuid,
            'title' => $resource->title,
            'text' => $resource->text,
            'text_color' => $resource->text_color,
            'sequence' => $resource->sequence,
            'duration' => $resource->duration,
            'button_title' => $resource->button_title,
            'url' => $resource->url,
            'file' => FileShortInfoResource::make($resource->file),
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
