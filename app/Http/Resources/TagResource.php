<?php

namespace App\Http\Resources;

use App\Models\Tag;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * @param Tag $tag
     * @return array
     */
    public function resource($tag)
    {
        return [
            'uuid' => $tag->uuid,
            'name' => $tag->name,
            'fixed_in_filters' => $tag->fixed_in_filters,
            'created_at' => $tag->created_at,
        ];
    }
}
