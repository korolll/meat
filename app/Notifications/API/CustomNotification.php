<?php

namespace App\Notifications\API;

use App\Services\Framework\Notifications\DatabaseNotificationDataTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class CustomNotification extends Notification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, DatabaseNotificationDataTrait;

    protected string $title;
    protected string $body;
    protected array $meta;

    /**
     * @param string $title
     * @param string $body
     * @param array  $meta
     */
    public function __construct(string $title, string $body, array $meta = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->meta = $meta;
    }


    /**
     * @param $notifiable
     *
     * @return string[]
     */
    public function via($notifiable)
    {
        return [
            FcmChannel::class,
            DatabaseChannel::class
        ];
    }

    /**
     * @param $notifiable
     *
     * @return \NotificationChannels\Fcm\FcmMessage
     */
    public function toFcm($notifiable)
    {
        $notification = \NotificationChannels\Fcm\Resources\Notification::create()
            ->setTitle($this->title)
            ->setBody($this->body);

        return FcmMessage::create()
            ->setData([
                'body_loc_args' => json_encode($this->meta ?: [])
            ])
            ->setNotification($notification);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'meta' => $this->meta
        ];
    }

    /**
     * @param $notifiable
     *
     * @return string
     */
    protected function getTitleForDbStorage($notifiable): string
    {
        return $this->title;
    }

    /**
     * @param $notifiable
     *
     * @return string
     */
    protected function getBodyForDbStorage($notifiable): string
    {
        return $this->body;
    }

    /**
     * @param $notifiable
     *
     * @return array
     */
    protected function getMetaForDbStorage($notifiable): array
    {
        return $this->meta;
    }
}
