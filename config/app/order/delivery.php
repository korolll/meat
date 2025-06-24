<?php

return [
    'use_distance_price'    =>  env('USE_DELIVERY_DISTANCE_PRICE', false),
    'max_distance' => env('APP_ORDER_DELIVERY_MAX_DISTANCE', 20000), // 20 km
    'use_max_distance_filter' => env('USE_MAX_DISTANCE_FILTER', false),
    'less_zone' => [
        'distance'  => env('LESS_DELIVERY_ZONE_DISTANCE', 6000),
        'price'     => env('LESS_DELIVERY_ZONE_PRICE', 250),
    ],
    'between_zones' => [
        'distance'  =>  env('BETWEEN_DELIVERY_ZONE_DISTANCE', 12000),
        'price' =>  env('BETWEEN_DELIVERY_ZONE_PRICE', 350),
    ],

    'more_zone' => [
        'price'  =>  env('MORE_DELIVERY_ZONE_PRICE',450)
    ],

    'use_free_first_delivery'    =>  env('USE_FREE_FIRST_DELIVERY', false),
];
