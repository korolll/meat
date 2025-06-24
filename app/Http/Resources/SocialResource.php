<?php

namespace App\Http\Resources;

use App\Models\Onboarding;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class SocialResource extends JsonResource
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
     * @param Social $social
     * @return array
     */
    public function resource($social)
    {
        return [
            'uuid' => $social->uuid,
            'title' => $social->title,
            'sort_number' => $social->sort_number,
            'url' => $social->url,
            'logo_file_uuid' => $social->logoFile->uuid,
            'logo_file_path' => Storage::url($social->logoFile->path),
            'created_at' => $social->created_at,
        ];
    }
}
