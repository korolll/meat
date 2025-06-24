<?php

namespace App\Services\Models\File;

use App\Contracts\Models\File\DeleteUnusedFilesContract;
use App\Contracts\Models\File\FindUnusedFilesContract;
use App\Jobs\DeleteFileJob;

class DeleteUnusedFiles implements DeleteUnusedFilesContract
{
    /**
     * @var \App\Contracts\Models\File\FindUnusedFilesContract
     */
    private $service;

    /**
     * @param \App\Contracts\Models\File\FindUnusedFilesContract $service
     */
    public function __construct(FindUnusedFilesContract $service)
    {
        $this->service = $service;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        $files = $this->service->find();

        foreach ($files as $file) {
            DeleteFileJob::dispatch($file);
        }

        return $files->count();
    }
}
