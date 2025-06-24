<?php

namespace App\Events;

use App\Models\File;
use Illuminate\Foundation\Events\Dispatchable;

class FileUploaded
{
    use Dispatchable;

    /**
     * @var \App\Models\File
     */
    public $file;

    /**
     * @param \App\Models\File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }
}
