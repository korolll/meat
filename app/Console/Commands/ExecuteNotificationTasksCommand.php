<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteNotificationTaskJob;
use App\Models\NotificationTask;
use Illuminate\Console\Command;

class ExecuteNotificationTasksCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'notification-tasks:execute';

    /**
     * @var string
     */
    protected $description = 'Выполняет задачи нотификаций';

    /**
     * @return void
     */
    public function handle()
    {
        $now = now();
        NotificationTask::whereNull('taken_to_work_at')
            ->where('execute_at', '<=', $now)
            ->each(function (NotificationTask $notificationTask) use ($now) {
                ExecuteNotificationTaskJob::dispatch($notificationTask);
                $notificationTask->taken_to_work_at = $now;
                $notificationTask->save();
            });
    }
}
