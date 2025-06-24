<?php

namespace App\Contracts\Models\File;

use App\Models\File;

interface DeleteFileContract
{
    /**
     * @param \App\Models\File $file
     */
    public function delete(File $file): void;
}
