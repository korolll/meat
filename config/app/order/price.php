<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Все что касается цены заказа располагается здесь
    |
    */

    'delivery' => [
        /**
         * @deprecated
         * @see \App\Services\Management\Client\Order\System\SystemOrderSettingStorageInterface
         */
        'free_threshold' => env('APP_ORDER_PRICE_DELIVERY_FREE_THRESHOLD', 1000),
        'price' => env('APP_ORDER_PRICE_DELIVERY_PRICE', 70),
    ],

    'bonus' => [
        'max_percent_to_pay' => env('APP_ORDER_PRICE_BONUS_MAX_PERCENT_TO_PAY', 10)
    ],

    /**
     * Config for
     * @see \App\Services\Management\Client\Product\Discount\Concrete\FirstOrderDiscountResolver
     */
    'first_order_discount_resolver_config' => [
        'discount_percent' => (float)env('APP_ORDER_PRICE_FODRC_DISCOUNT_PERCENT', 15),
    ]
];
