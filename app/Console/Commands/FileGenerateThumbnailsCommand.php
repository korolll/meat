<?php

namespace App\Console\Commands;

use App\Events\FileUploaded;
use App\Listeners\GenerateFileThumbnails;
use App\Models\File;
use Illuminate\Console\Command;

class FileGenerateThumbnailsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'file:generate-thumbnails';

    /**
     * @var string
     */
    protected $description = 'Генерирует миниатюры для уже загруженных файлов';

    /**
     * @return void
     */
    public function handle()
    {
        File::each(function (File $file) {
            app(GenerateFileThumbnails::class)->handle(new FileUploaded($file));
        });
    }
}
