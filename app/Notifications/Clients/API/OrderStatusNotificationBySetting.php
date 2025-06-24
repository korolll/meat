<?php

namespace App\Notifications\Clients\API;

use App\Models\Order;
use App\Models\OrderNotificationSetting;
use App\Models\OrderStatus;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class OrderStatusNotificationBySetting extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, Queueable, SerializesModels;

    public Order $order;
    public OrderNotificationSetting $setting;
    public OrderStatus $status;

    private ?array $resolvedMessages = null;

    /**
     * @param Order $order
     * @param OrderNotificationSetting $setting
     * @param OrderStatus $status
     */
    public function __construct(Order $order, OrderNotificationSetting $setting, OrderStatus $status)
    {
        $this->order = $order;
        $this->setting = $setting;
        $this->status = $status;
    }


    /**
     * @param $notifiable
     *
     * @return string[]
     */
    public function via($notifiable)
    {
        $result = [];

        $resolved = $this->resolveMessages();
        if (isset($resolved['notification_sms'])) {
            $result[] = 'sms';
        }
        if (isset($resolved['notification_mail'])) {
            $result[] = 'mail';
        }
        if (isset($resolved['notification_push'])) {
            $result[] = FcmChannel::class;
        }
        if (isset($resolved['notification_database'])) {
            $result[] = DatabaseChannel::class;
        }

        return $result;
    }

    /**
     * @param mixed $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        $resolved = $this->resolveMessages();
        return $resolved['notification_sms']['body'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resolved = $this->resolveMessages();
        $resolvedData = $resolved['notification_mail'];

        return (new MailMessage)
            ->subject($resolvedData['title'])
            ->line($resolvedData['body']);
    }

    /**
     * @param $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        $resolved = $this->resolveMessages();
        $resolvedData = $resolved['notification_database'];

        return [
            'title' => $resolvedData['title'],
            'body' => $resolvedData['body'],
            'meta' => $this->buildMeta()
        ];
    }

    /**
     * @param $notifiable
     *
     * @return \NotificationChannels\Fcm\FcmMessage
     */
    public function toFcm($notifiable)
    {
        $resolved = $this->resolveMessages();
        $resolvedData = $resolved['notification_push'];

        $notification = \NotificationChannels\Fcm\Resources\Notification::create()
            ->setTitle($resolvedData['title'])
            ->setBody($resolvedData['body']);

        return FcmMessage::create()
            ->setData([
                'body_loc_args' => json_encode($this->buildMeta())
            ])
            ->setNotification($notification);
    }

    protected function buildMeta(): array
    {
        return [
            'type' => 'orders',
            'id' => $this->order->uuid
        ];
    }

    protected function resolveVars(array $vars): array
    {
        $result = [];

        // Add new vars HERE if it needs
        if (isset($vars['orderNumber'])) {
            $result['orderNumber'] = $this->order->number;
        }
        if (isset($vars['statusName'])) {
            $result['statusName'] = $this->status->name;
        }

        return $result;
    }

    /**
     * @return array{
     *     notification_sms?: array{title: string, bbody: string},
     *     notification_mail?: array{title: string, body: string},
     *     notification_push?: array{title: string, body: string},
     *     notification_database?: array{title: string, body: string},
     * }
     */
    protected function resolveMessages(): array
    {
        if ($this->resolvedMessages !== null) {
            return $this->resolvedMessages;
        }

        $result = [];
        $resultVars = [];
        $props = [
            'notification_sms',
            'notification_mail',
            'notification_push',
            'notification_database',
        ];

        $vars = [];
        foreach ($props as $prop) {
            $data = $this->resolveMessageData($prop);
            if (!$data) {
                continue;
            }

            foreach ($data as $key => $message) {
                $newVars = $this->collectVars($message);
                $resultVars[$prop][$key] = $newVars;
                $vars += array_flip($newVars);
            }

            $result[$prop] = $data;
        }

        $resolvedVars = $this->resolveVars($vars);
        foreach ($result as $prop => &$data) {
            foreach ($data as $key => &$message) {
                $messageVars = $resultVars[$prop][$key];
                $message = $this->applyVars($message, $messageVars, $resolvedVars);
            }
        }

        $this->resolvedMessages = $result;
        return $result;
    }

    protected function applyVars(string $message, array $messageVars, array $resolvedVars): string
    {
        foreach ($messageVars as $var) {
            $varValue = $resolvedVars[$var] ?? 'unk';
            $toSearch = '{$' . $var . '}';
            $message = str_replace($toSearch, $varValue, $message);
        }

        return $message;
    }

    protected function resolveMessageData(string $property): ?array
    {
        $data = $this->setting->{$property};
        if (!$data) {
            return null;
        }

        $body = Arr::get($data, 'body');
        if (!$body || !is_string($body)) {
            return null;
        }

        $title = Arr::get($data, 'title') ?: 'Обновлен статус заказа N{$orderNumber}';
        return [
            'title' => $title,
            'body' => $body,
        ];
    }

    protected function collectVars(string $message): array
    {
        $pattern = '/{\$([a-zA-Z]+)}/';
        $matches = [];
        preg_match_all($pattern, $message, $matches);

        return $matches[1];
    }
}