<?php

namespace App\Http\Resources;

use App\Models\PromoDescription;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class PromoDescriptionResource extends JsonResource
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
     * @param PromoDescription $promoDescription
     * @return array
     */
    public function resource($promoDescription)
    {
        $logoFile = $promoDescription->logoFile;
        return [
            'uuid' => $promoDescription->uuid,
            'name' => $promoDescription->name,
            'title' => $promoDescription->title,
            'description' => $promoDescription->description,

            'logo_file_uuid' => $logoFile ? $logoFile->uuid : null,
            'logo_file_path' => $logoFile ? Storage::url($logoFile->path) : null,

            'discount_type' => $promoDescription->discount_type,
            'color' => $promoDescription->color,
            'is_hidden' => $promoDescription->is_hidden,
            'subtitle' => $promoDescription->subtitle,

            'created_at' => $promoDescription->created_at,
            'updated_at' => $promoDescription->updated_at,
        ];
    }
}
