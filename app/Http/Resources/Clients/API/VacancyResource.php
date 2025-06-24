<?php

namespace App\Http\Resources\Clients\API;

use App\Models\Vacancy;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class VacancyResource extends JsonResource
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
     * @param Vacancy $vacancy
     * @return array
     */
    public function resource($vacancy)
    {
        return [
            'uuid' => $vacancy->uuid,
            'title' => $vacancy->title,
            'sort_number' => $vacancy->sort_number,
            'url' => $vacancy->url,
            'logo_file_uuid' => $vacancy->logoFile->uuid,
            'logo_file_path' => Storage::url($vacancy->logoFile->path),
            'created_at' => $vacancy->created_at,
        ];
    }
}
