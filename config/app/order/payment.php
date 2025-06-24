<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Все что касается оплаты заказа располагается здесь
    |
    */

    'enable_new_data' => env('APP_ORDER_PAYMENT_ENABLE_NEW_DATA', 0),
    'enable_new_data_for_clients' => array_flip(explode(',', env('APP_ORDER_PAYMENT_ENABLE_NEW_DATA_FOR_CLIENTS', ''))),
];
