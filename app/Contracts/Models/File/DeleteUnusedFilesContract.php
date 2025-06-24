<?php

namespace App\Contracts\Models\File;

interface DeleteUnusedFilesContract
{
    /**
     * @return int
     */
    public function delete(): int;
}
