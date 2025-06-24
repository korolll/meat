<?php

namespace App\Http\Resources;

use App\Models\Tag;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class TagNameResource extends JsonResource
{
    /**
     * @param Tag $tag
     * @return array
     */
    public function resource($tag)
    {
        return $tag->name;
    }
}
