<?php

namespace App\Providers\Models;

use App\Contracts\Models\File\DeleteFileContract;
use App\Contracts\Models\File\DeleteUnusedFilesContract;
use App\Contracts\Models\File\FindUnusedFilesContract;
use App\Services\Models\File\DeleteFile;
use App\Services\Models\File\DeleteUnusedFiles;
use App\Services\Models\File\FindUnusedFiles;
use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    public $singletons = [
        DeleteFileContract::class => DeleteFile::class,
        DeleteUnusedFilesContract::class => DeleteUnusedFiles::class,
        FindUnusedFilesContract::class => FindUnusedFiles::class,
    ];

    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
