<?php

namespace App\Http\Resources;

use App\Models\File;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AssortmentFileResource extends JsonResource
{
    /**
     * @param File $file
     * @return array
     */
    public function resource($file)
    {
        return [
            'uuid' => $file->uuid,
            'file_category_id' => $file->file_category_id,
            'path' => Storage::url($file->path),
            'thumbnails' => collect($file->thumbnails)->map(function ($path) {
                return Storage::url($path);
            }),
        ];
    }
}
