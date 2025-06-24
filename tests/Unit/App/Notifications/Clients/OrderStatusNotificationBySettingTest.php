<?php

namespace Tests\Unit\App\Notifications\Clients;

use App\Models\Order;
use App\Models\OrderDeliveryType;
use App\Models\OrderNotificationSetting;
use App\Models\OrderStatus;
use App\Notifications\Clients\API\OrderStatusNotificationBySetting;
use Illuminate\Notifications\Channels\DatabaseChannel;
use NotificationChannels\Fcm\FcmChannel;
use Ramsey\Uuid\Uuid;
use Tests\TestCaseNotificationsFake;

class OrderStatusNotificationBySettingTest extends TestCaseNotificationsFake
{
    public function testAllMessages()
    {
        $orderNumber = $this->faker->numberBetween(1, 10);
        $deliveryTypeId = $this->faker->randomElement([
            OrderDeliveryType::ID_DELIVERY,
            OrderDeliveryType::ID_PICKUP,
        ]);
        $order = new Order();
        $order->uuid = Uuid::uuid4()->toString();
        $order->number = $orderNumber;
        $order->order_delivery_type_id = $deliveryTypeId;

        /** @var OrderStatus $status */
        $status = OrderStatus::find($this->faker->randomElement([
            OrderStatus::ID_NEW,
            OrderStatus::ID_DELIVERING,
            OrderStatus::ID_COLLECTING,
            OrderStatus::ID_COLLECTED,
            OrderStatus::ID_DELIVERING,
            OrderStatus::ID_DONE,
        ]));
        $statusName = $status->name;

        $smsText = '111 {$orderNumber}';
        $pushText = '222 {$orderNumber} AND {$statusName}';
        $pushTitle = '{$orderNumber}';
        $databaseText = '333 {$orderNumber} AND {$statusName} AND {$orderNumber}';
        $mailText = '444 Lol';

        $defTitle = 'Обновлен статус заказа N' . $orderNumber;
        $defMeta = [
            'type' => 'orders',
            'id' => $order->uuid
        ];

        $setting = new OrderNotificationSetting([
            'order_status_id' => $status->id,
            'order_delivery_type_id' => $deliveryTypeId,
            'notification_sms' => ['body' => $smsText],
            'notification_mail' => ['body' => $mailText],
            'notification_push' => ['body' => $pushText, 'title' => $pushTitle],
            'notification_database' => ['body' => $databaseText],
        ]);
        $notification = new OrderStatusNotificationBySetting($order, $setting, $status);

        $notifiable = [];
        $this->assertEquals([
            'sms',
            'mail',
            FcmChannel::class,
            DatabaseChannel::class,
        ], $notification->via($notifiable));

        // Test sms
        $sms = $notification->toSms($notifiable);
        $this->assertEquals("111 $orderNumber", $sms);

        // Test mail
        $mail = $notification->toMail($notifiable);
        $this->assertEquals($mailText, $mail->introLines[0]);
        $this->assertEquals($defTitle, $mail->subject);

        // Test push
        $push = $notification->toFcm($notifiable);
        $pushNotification = $push->getNotification();
        $this->assertEquals("222 {$orderNumber} AND {$statusName}", $pushNotification->getBody());
        $this->assertEquals("{$orderNumber}", $pushNotification->getTitle());
        $this->assertEquals($defMeta, $push->getData());

        // Test database
        $database = $notification->toDatabase($notifiable);
        $this->assertEquals([
            'title' => $defTitle,
            'body' => "333 {$orderNumber} AND {$statusName} AND {$orderNumber}",
            'meta' => $defMeta
        ], $database);
    }
}