<?php

namespace App\Jobs;

use App\Contracts\Models\File\DeleteFileContract;
use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteFileJob implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /**
     * @var \App\Models\File
     */
    private $file;

    /**
     * @param \App\Models\File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @param \App\Contracts\Models\File\DeleteFileContract $service
     */
    public function handle(DeleteFileContract $service): void
    {
        $service->delete($this->file);
    }
}
