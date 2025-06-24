<?php

namespace App\Http\Resources\Clients\API;

use App\Models\Onboarding;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class OnboardingResource extends JsonResource
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
     * @param Onboarding $onboarding
     * @return array
     */
    public function resource($onboarding)
    {
        return [
            'uuid' => $onboarding->uuid,
            'title' => $onboarding->title,
            'sort_number' => $onboarding->sort_number,
            'logo_file_uuid' => $onboarding->logoFile->uuid,
            'logo_file_path' => Storage::url($onboarding->logoFile->path),
            'created_at' => $onboarding->created_at,
        ];
    }
}
