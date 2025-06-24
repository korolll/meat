<?php

namespace App\Console\Commands;

use App\Contracts\Models\File\DeleteUnusedFilesContract;
use Illuminate\Console\Command;

class FileDeleteUnusedCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'file:delete-unused';

    /**
     * @var string
     */
    protected $description = 'Удаляет неспользуемые файлы';

    /**
     * @param \App\Contracts\Models\File\DeleteUnusedFilesContract $service
     */
    public function handle(DeleteUnusedFilesContract $service): void
    {
        $this->info('Запланировано удаление файлов: ' . $service->delete());
    }
}
