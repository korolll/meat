<?php

use App\Models\Client;
use App\Models\User;
use App\Models\UserType;
use App\Models\OrderStatus;

$ruleOnlineOrderShouldBePaid = ['check' => 'shouldBePaidOnline'];
return [

    /*
    |--------------------------------------------------------------------------
    | Правила переходов статусов заказа
    |--------------------------------------------------------------------------
    |
    | ...
    |
    |
    | Вид:
    | - Модель
    | - - Тип пользователя (только для User модели)
    | - - - Текущий статус
    | - - - - Возможный статус
    |
    */

    Client::class => [
        OrderStatus::ID_NEW => [
            OrderStatus::ID_CANCELLED => 1,
        ],
    ],

    User::class => [
        UserType::ID_ADMIN => [
            OrderStatus::ID_NEW => [
                OrderStatus::ID_COLLECTING => 1,
                OrderStatus::ID_CANCELLED => 1,
            ],
            OrderStatus::ID_COLLECTING => [
                OrderStatus::ID_COLLECTED => 1,
                OrderStatus::ID_CANCELLED => 1,
            ],
            OrderStatus::ID_COLLECTED => [
                OrderStatus::ID_DELIVERING => $ruleOnlineOrderShouldBePaid,
                OrderStatus::ID_DONE => 1, //Временно разрешил выставлять статус выполнен, даже если нет оплаты.
                OrderStatus::ID_CANCELLED => 1,
            ],
            OrderStatus::ID_DELIVERING => [
                OrderStatus::ID_DONE => $ruleOnlineOrderShouldBePaid,
                OrderStatus::ID_CANCELLED => 1,
            ],
            OrderStatus::ID_DONE => [
                OrderStatus::ID_CANCELLED => 1
            ]
        ],

        UserType::ID_STORE => [
            OrderStatus::ID_COLLECTING => [
                OrderStatus::ID_COLLECTED => 1,
            ],
            OrderStatus::ID_COLLECTED => [
                OrderStatus::ID_DELIVERING => $ruleOnlineOrderShouldBePaid,
            ],
        ]
    ],
];
