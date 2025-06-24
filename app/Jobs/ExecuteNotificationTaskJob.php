<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\NotificationTask;
use App\Notifications\API\CustomNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExecuteNotificationTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\NotificationTask
     */
    public NotificationTask $notificationTask;

    /**
     * @param \App\Models\NotificationTask $notificationTask
     */
    public function __construct(NotificationTask $notificationTask)
    {
        $this->notificationTask = $notificationTask;
        $this->queue = 'notifications';
    }

    /**
     * @return void
     */
    public function handle()
    {
        $exist = $this->notificationTask->clients()->exists();
        if ($exist) {
            $query = $this->notificationTask->clients();
        } else {
            $query = Client::query();
        }

        $query->each(function (Client $client) {
            $this->handleClient($client);
        });

        $this->notificationTask->executed_at = now();
        $this->notificationTask->save();
    }

    /**
     * @param \App\Models\Client $client
     *
     * @return void
     */
    protected function handleClient(Client $client)
    {
        $title = $this->resolveTemplate($client, $this->notificationTask->title_template);
        $body = $this->resolveTemplate($client, $this->notificationTask->body_template);

        $notification = new CustomNotification(
            $title,
            $body,
            (array)Arr::get($this->notificationTask->options, 'meta', [])
        );

        $client->notify($notification);
    }

    /**
     * @param \App\Models\Client $client
     * @param string             $template
     *
     * @return string
     */
    protected function resolveTemplate(Client $client, string $template): string
    {
        if (! Str::containsAll($template, ['{', '}'])) {
            return $template;
        }

        $rules = [
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
        ];

        foreach ($rules as $field => $value) {
            $rep = "{{$field}}";
            $template = Str::replace($rep, $value, $template);
        }

        return $template;
    }
}
