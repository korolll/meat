<?php

namespace App\Listeners;

use App\Events\FileUploaded;
use App\Jobs\GenerateImageFileThumbnails;

class GenerateFileThumbnails
{
    /**
     * @param \App\Events\FileUploaded $event
     */
    public function handle(FileUploaded $event)
    {
        if ($event->file->isImage()) {
            GenerateImageFileThumbnails::dispatch($event->file);
        }
    }
}
