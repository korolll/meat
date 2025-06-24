<?php

namespace App\Services\Framework\Notifications;

trait DatabaseNotificationDataTrait
{
    protected abstract function getTitleForDbStorage($notifiable): string;
    protected abstract function getBodyForDbStorage($notifiable): string;
    protected abstract function getMetaForDbStorage($notifiable): array;

    /**
     * @param $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->getTitleForDbStorage($notifiable),
            'body' => $this->getBodyForDbStorage($notifiable),
            'meta' => $this->getMetaForDbStorage($notifiable)
        ];
    }
}
