<?php

use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;

return [

    /*
    |--------------------------------------------------------------------------
    | Все что взаимодейтсвия с iiko
    |
    */

    // Включение отправок
    'enable_sending' => env('APP_ORDER_IIKO_ENABLE_SENDING', false),

    // Конфиг синхронизации ТИП_ОПЛАТЫ => IIKO->code для поиска в методе api/1/payment_types
    'payment_types_codes' => [
        OrderPaymentType::ID_CASH => env('APP_ORDER_IIKO_PAYMENT_TYPES_CODES_CASH', 'CASH'),
        OrderPaymentType::ID_ONLINE => env('APP_ORDER_IIKO_PAYMENT_TYPES_CODES_ONLINE', 'ONLINE')
    ],

    // Конфиг синхронизации ТИП_ОПЛАТЫ + ТИП_ДОСТАВКИ => IIKO->orderServiceType для поиска в методе api/1/deliveries/order_types
    'order_types' => [
        OrderPaymentType::ID_CASH => [
            OrderDeliveryType::ID_DELIVERY => env('APP_ORDER_IIKO_ORDER_TYPES_CASH_DELIVERY', 'DeliveryPickUp'),
            OrderDeliveryType::ID_PICKUP => env('APP_ORDER_IIKO_ORDER_TYPES_CASH_PICKUP', 'Common'),
        ],
        OrderPaymentType::ID_ONLINE => [
            OrderDeliveryType::ID_DELIVERY => env('APP_ORDER_IIKO_ORDER_TYPES_ONLINE_DELIVERY', 'DeliveryByCourier'),
            OrderDeliveryType::ID_PICKUP => env('APP_ORDER_IIKO_ORDER_TYPES_ONLINE_PICKUP', 'Common'),
        ]
    ],
];
