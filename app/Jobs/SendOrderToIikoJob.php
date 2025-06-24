<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Services\Integrations\Iiko\IikoClientInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderToIikoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Order
     */
    protected Order $order;

    /**
     * @param \App\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return void
     */
    public function handle(IikoClientInterface $client)
    {
        try {
            $data = $this->makePaymentData($this->order, $client);
        } catch (Throwable $exception) {
            Log::channel('iiko')->error('Не удалось создать данные для синхронизации по заказу', [
                'order_uuid' => $this->order->uuid,
                'exception' => $exception
            ]);

            return;
        }

        Log::channel('iiko')->debug('Создали данные для платежа', [
            'order_uuid' => $this->order->uuid,
            'data' => $data
        ]);
        $success = $this->sendPayment($data, $client);
        if ($success) {
            Log::channel('iiko')->debug('Платеж успешно отправлен', [
                'order_uuid' => $this->order->uuid,
            ]);
        }
    }

    protected function makePaymentData(Order $order, IikoClientInterface $client): array
    {
        list($paymentTypeId, $terminalId, $paymentTypeKind) = $this->findPaymentTypeIdAndCashId($order, $client);
        $orderTypeId = $this->findOrderTypeId($order, $client);
        $orderItems = $this->makeOrderItems($order);

        $orderClient = $order->client;
        /**
         * @see https://api-ru.iiko.services/#tag/Deliveries:-Create-and-update/paths/~1api~11~1deliveries~1create/post
         */
        return [
            'organizationId' => $order->store_user_uuid,
            'terminalGroupId' => $terminalId,
            'createOrderSettings' => [
                'mode' => 'Async' // ????
            ],
            'order' => [
                'items' => $orderItems,
                'payments' => [[
                    'paymentTypeKind' => $paymentTypeKind,
                    'sum' => $order->total_price,
                    'paymentTypeId' => $paymentTypeId
                ]],
                'orderTypeId' => $orderTypeId,
                'phone' => $orderClient->phone,
                'customer' => [
                    'name' => $orderClient->name ?: 'NotSpecified',
                    'comment' => json_encode($order->client_address_data) ?: '',
                    'surname' => '', // ????
                    'email' => $orderClient->email ?: '',
                    'gender' => 'NotSpecified',
                ]
            ],
        ];
    }

    protected function makeOrderItems(Order $order): array
    {
        $result = [];
        $products = $order
            ->orderProducts()
            ->with('product')
            ->get();

        /** @var \App\Models\OrderProduct $product */
        foreach ($products as $product) {
            if ($product->quantity != 0) {
                if ($this->isMarkedProduct($product)) {
                    $result = array_merge($result, $this->createMultipleOrderItems($product));
                } else {
                    $result[] = $this->createOrderItem($product, $product->quantity);
                }
            }
        }

        if ($order->delivery_price && $order->delivery_price > 0) {
            $deliveryId = config('services.iiko.delivery_product_id');
            if ($deliveryId) {
                $result[] = [
                    'type' => 'Product',
                    'productId' => $deliveryId,
                    'price' => $order->delivery_price,
                    'amount' => 1
                ];
            }
        }

        return $result;
    }

    protected function isMarkedProduct(OrderProduct $orderProduct): ?bool
    {
        return $orderProduct
            ->product()
            ->first()
            ->assortment()
            ->first()
            ->assortmentMarkedProperties()
            ->exists();
    }

    private function createOrderItem($product, $amount): array
    {
        return [
            'type' => 'Product',
            'productId' => $product->product->assortment_uuid,
            'price' => $product->price_with_discount,
            'amount' => $amount
        ];
    }

    private function createMultipleOrderItems($product): array
    {
        $items = [];
        for ($i = 1; $i <= $product->quantity; $i++) {
            $items[] = $this->createOrderItem($product, 1);
        }
        return $items;
    }

    protected function sendPayment(array $data, IikoClientInterface $client): bool
    {
        $result = $client->createOrder($data);
        if (isset($result['error'])) {
            Log::channel('iiko')->error('Не удалось создать заказ', [
                'order_uuid' => $this->order->uuid,
                'result' => $result
            ]);

            return false;
        }

        $orderInfo = Arr::get($result, 'orderInfo');
        if ($orderInfo && isset($orderInfo['errorInfo']) && $orderInfo['errorInfo']) {
            Log::channel('iiko')->error('Заказ создан, но с ошибкой', [
                'order_uuid' => $this->order->uuid,
                'result' => $result
            ]);

            return false;
        }

        return true;
    }

    protected function findPaymentTypeIdAndCashId(Order $order, IikoClientInterface $client): array
    {
        $configMap = config('app.order.iiko.payment_types_codes');
        $targetCode = Arr::get($configMap, $order->order_payment_type_id);
        if (! $targetCode) {
            throw new \Exception('Не найдено в конфигурации нужно кода для поиска типа оплаты: ' . $order->order_payment_type_id);
        }

        $paymentTypes = $client->getPaymentTypes([$order->store_user_uuid]);
        if (! $paymentTypes) {
            throw new \Exception('Не найдены типы оплаты iiko');
        }

        foreach ($paymentTypes as $paymentType) {
            $code = Arr::get($paymentType, 'code');
            if ($code === $targetCode) {
                $paymentTypeId = Arr::get($paymentType, 'id');
                if (! $paymentTypeId) {
                    throw new \Exception('Не найден id типа оплаты в найденном типе оплаты');
                }

                $terminals = Arr::get($paymentType, 'terminalGroups', []);
                if (! $terminals) {
                    throw new \Exception('Не найдены кассы в найденном типе оплаты');
                }

                $terminalId = Arr::get($terminals, '0.id');
                if (! $terminalId) {
                    throw new \Exception('Не найден id кассы в найденном типе оплаты');
                }

               $paymentTypeKind = Arr::get($paymentType, 'paymentTypeKind');
                if (! $paymentTypeKind) {
                    throw new \Exception('Не найден paymentTypeKind в найденном типе оплаты');
                }

                return [(string)$paymentTypeId, (string)$terminalId, (string)$paymentTypeKind];
            }
        }

        throw new \Exception('Не подобран тип оплаты');
    }


    protected function findOrderTypeId(Order $order, IikoClientInterface $client): string
    {
        $configMap = config('app.order.iiko.order_types');
        $findKey = $order->order_payment_type_id . '.' . $order->order_delivery_type_id;
        $targetType = Arr::get($configMap, $findKey);
        if (! $targetType) {
            throw new \Exception('Не найдено в конфигурации нужно типа для поиска типа заказа: ' . $findKey);
        }

        $orderTypes = $client->getOrderTypes([$order->store_user_uuid]);
        $items = Arr::get($orderTypes, '0.items', []);
        if (! $items) {
            throw new \Exception('Не найдены типы заказов iiko');
        }

        foreach ($items as $item) {
            $type = Arr::get($item, 'id');
            if ($type === $targetType) {
                $orderTypeId = Arr::get($item, 'id');
                if (! $orderTypeId) {
                    throw new \Exception('Не найден id типа заказа в найденном типе заказа');
                }

                return (string)$orderTypeId;
            }
        }

        throw new \Exception('Не подобран тип заказа');
    }
}
