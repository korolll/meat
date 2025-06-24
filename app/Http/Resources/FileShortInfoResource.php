<?php

namespace App\Http\Resources;

use App\Models\File;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileShortInfoResource extends JsonResource
{
    /**
     * @param File $file
     * @return array
     */
    public function resource($file)
    {
        $publicName = object_get($file, 'pivot.public_name', null);

        return [
            'uuid' => $file->uuid,
            'path' => Storage::url($file->path),
            'public_name' => $this->when($publicName !== null, $publicName),
            'thumbnails' => collect($file->thumbnails)->map(function ($path) {
                return Storage::url($path);
            }),
        ];
    }
}
