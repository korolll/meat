<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeMetaCommand extends Command
{
    /**
     * Название команды
     *
     * @var string
     */
    protected $signature = 'make:meta';

    /**
     * Описание команды
     *
     * @var string
     */
    protected $description = 'Update ide-helper generated files';

    /**
     * Список команд на запуск
     *
     * @var array
     */
    protected $commands = [
        'ide-helper:generate',
        'ide-helper:macros',
        'ide-helper:meta',
        'ide-helper:models --nowrite',
    ];

    /**
     * Execute the console command
     */
    public function handle()
    {
        if (app()->environment() !== 'production') {
            if (file_exists('.env')) {
                foreach ($this->commands as $command) {
                    $this->getOutput()->writeln($this->artisan($command));
                }
            } else {
                $this->error('Meta generation skipped, we could not find a .env file');
            }
        }
    }

    /**
     * Запускает artisan команду
     *
     * @param string $command
     * @return mixed
     */
    protected function artisan($command)
    {
        // Фасад Artisan не используется умышленно, есть проблемы с запуском команд ide-helper
        exec("php artisan {$command}", $output);

        return $output;
    }
}
