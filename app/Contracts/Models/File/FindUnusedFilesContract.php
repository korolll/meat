<?php

namespace App\Contracts\Models\File;

use Illuminate\Support\Collection;

interface FindUnusedFilesContract
{
    /**
     * @return \Illuminate\Support\Collection|\App\Models\File[]
     */
    public function find(): Collection;
}
