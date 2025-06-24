<?php

use App\Models\OrderDeliveryType;
use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableOrderNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_notification_settings', function (Blueprint $table) {
            $table->string('order_status_id')->nullable(false);
            $table->string('order_delivery_type_id')->nullable(false);

            $table->primary([
                'order_status_id',
                'order_delivery_type_id',
            ]);

            $table->foreign('order_status_id')->references('id')->on('order_statuses')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('order_delivery_type_id')->references('id')->on('order_delivery_types')->onDelete('RESTRICT')->onUpdate('CASCADE');

            $table->jsonb('notification_sms')->nullable();
            $table->jsonb('notification_mail')->nullable();
            $table->jsonb('notification_push')->nullable();
            $table->jsonb('notification_database')->nullable();
        });

        $this->migrateNotifications();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_notification_settings');
    }

    protected function migrateNotifications()
    {
        $toInsert = array_merge(
            $this->generateInsertsForOrderStatusUpdated(),
            $this->generateInsertsForOrderIsCreated(),
            $this->generateInsertsForOrderIsCollected(),
            $this->generateInsertsForOrderIsCancelled(),
        );

        // Migrate current statuses
        DB::table('order_notification_settings')->insert($toInsert);
    }

    protected function generateInsertsForOrderStatusUpdated(): array
    {
        $commonStatusChangedJson = $this->buildCommonNotificationJson();
        $toInsert = [];
        $statusesToMigrateCommon = [
            OrderStatus::ID_COLLECTING,
            OrderStatus::ID_DELIVERING,
            OrderStatus::ID_DONE,
        ];

        $data = [
            'notification_push' => $commonStatusChangedJson,
            'notification_database' => $commonStatusChangedJson,
            'notification_sms' => null,
            'notification_mail' => null,
        ];
        foreach ($statusesToMigrateCommon as $statusId ) {
            $data['order_status_id'] = $statusId;
            foreach (OrderDeliveryType::ALL as $deliveryType) {
                $data['order_delivery_type_id'] = $deliveryType;
                $toInsert[] = $data;
            }
        }

        return $toInsert;
    }

    protected function generateInsertsForOrderIsCreated(): array
    {
        $body = 'Ваш заказ создан. Номер заказа: N{$orderNumber}';
        $toInsert = [];
        $data = [
            'notification_push' => null,
            'notification_database' => null,
            'notification_sms' => $this->buildNotificationJson(
                null,
                $body
            ),
            'notification_mail' => $this->buildNotificationJson(
                'Новый заказ',
                $body
            ),
            'order_status_id' => OrderStatus::ID_NEW,
        ];

        foreach (OrderDeliveryType::ALL as $deliveryType) {
            $data['order_delivery_type_id'] = $deliveryType;
            $toInsert[] = $data;
        }

        return $toInsert;
    }

    protected function generateInsertsForOrderIsCollected(): array
    {
        $body = 'Ваш заказ N{$orderNumber} собран';
        $commonStatusChangedJson = $this->buildCommonNotificationJson();

        $toInsert = [];
        $data = [
            'notification_push' => $commonStatusChangedJson,
            'notification_database' => $commonStatusChangedJson,
            'notification_sms' => $this->buildNotificationJson(
                null,
                $body
            ),
            'notification_mail' => $this->buildNotificationJson(
                'Обновление статуса заказа N{$orderNumber}',
                $body
            ),
            'order_status_id' => OrderStatus::ID_COLLECTED,
        ];

        foreach (OrderDeliveryType::ALL as $deliveryType) {
            $data['order_delivery_type_id'] = $deliveryType;
            $toInsert[] = $data;
        }

        return $toInsert;
    }

    protected function generateInsertsForOrderIsCancelled(): array
    {
        $body = 'Ваш заказ N{$orderNumber} отменен';

        $commonStatusChangedJson = $this->buildCommonNotificationJson();
        $toInsert = [];
        $data = [
            'notification_push' => $commonStatusChangedJson,
            'notification_database' => $commonStatusChangedJson,
            'notification_sms' => $this->buildNotificationJson(
                null,
                $body
            ),
            'notification_mail' => $this->buildNotificationJson(
                'Обновление статуса заказа N{$orderNumber}',
                $body
            ),
            'order_status_id' => OrderStatus::ID_CANCELLED,
        ];

        foreach (OrderDeliveryType::ALL as $deliveryType) {
            $data['order_delivery_type_id'] = $deliveryType;
            $toInsert[] = $data;
        }

        return $toInsert;
    }

    protected function buildNotificationJson(?string $title, string $body): string
    {
        if (!$title) {
            return json_encode(['body' => $body]);
        }

        return json_encode([
            'title' => $title,
            'body' => $body
        ]);
    }

    protected function buildCommonNotificationJson(): string
    {
        $commonStatusChangedTitle = 'Обновлен статус заказа N{$orderNumber}';
        $commonStatusChangedBody = 'Ваш заказ в статусе \'{$statusName}\'';

        return $this->buildNotificationJson(
            $commonStatusChangedTitle,
            $commonStatusChangedBody
        );
    }
}
