<?php

namespace App\Http\Resources;

use App\Models\File;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileResource extends JsonResource
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
            'original_name' => $file->original_name,
            'path' => Storage::url($file->path),
            'mime_type' => $file->mime_type, // nullable
            'size' => $file->size,
        ];
    }
}
