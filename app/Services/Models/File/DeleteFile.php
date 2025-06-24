<?php

namespace App\Services\Models\File;

use App\Contracts\Models\File\DeleteFileContract;
use App\Models\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class DeleteFile implements DeleteFileContract
{
    /**
     * @param \App\Models\File $file
     * @throws \Throwable
     */
    public function delete(File $file): void
    {
        $file->delete();
        $this->deleteFromDisk($file);
    }

    /**
     * @param \App\Models\File $file
     */
    private function deleteFromDisk(File $file): void
    {
        Storage::delete($this->getPaths($file));
    }

    /**
     * @param \App\Models\File $file
     * @return array
     */
    private function getPaths(File $file): array
    {
        $paths = [$file->path];

        return array_merge($paths, Arr::flatten($file->thumbnails));
    }
}
