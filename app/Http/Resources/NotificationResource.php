<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @param \Illuminate\Notifications\DatabaseNotification $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'id' => $resource->id,
            'type' => $resource->type,
            'data' => $resource->data,
            'created_at' => $resource->created_at,
            'read_at' => $resource->read_at,
            'deleted_at' => $resource->deleted_at,
        ];
    }
}
