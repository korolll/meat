<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Данные для управления акцией "Я в магазине"
    |--------------------------------------------------------------------------
    |
    | offer_delay - предлагать товары, со времени покупки которых прошло уже N дней
    | tracking_period - прекратить отслеживание товаров для offer_delay, через N дней после последней покупки
    |
    */

    'in_the_shop' => [
        'discount' => (float)env('PROMOTIONS_IN_THE_SHOP_DISCOUNT', 5),
        'offer_delay' => env('PROMOTIONS_IN_THE_SHOP_OFFER_DELAY_IN_DAYS', 7),
        'tracking_period' => env('PROMOTIONS_IN_THE_SHOP_TRACKING_PERIOD_IN_DAYS', 60),
        'assortment_property_uuid' => env('PROMOTIONS_IN_THE_SHOP_ASSORTMENT_PROPERTY_UUID'),
        'property_new' => env('PROMOTIONS_IN_THE_SHOP_ASSORTMENT_PROPERTY_NEW'),
        'property_sale' => env('PROMOTIONS_IN_THE_SHOP_ASSORTMENT_PROPERTY_SALE'),
    ],
];
