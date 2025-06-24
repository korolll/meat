<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Правила переходов статусов заявок на товары
    |--------------------------------------------------------------------------
    |
    | ...
    |
    */

    'product_request_customer_status_id' => [
        'new' => [
            'user-canceled' => [
                'product_request_customer_status_id' => 'user-canceled',
                'product_request_supplier_status_id' => 'user-canceled',
                'product_request_delivery_status_id' => 'canceled',
            ],
        ],
        'on-the-way' => [
            'done' => [
                'product_request_customer_status_id' => 'done',
                'product_request_delivery_status_id' => 'done',
            ],
        ],
        'matching' => [
            'new' => [
                'product_request_customer_status_id' => 'new',
                'product_request_supplier_status_id' => 'new',
            ],
            'user-canceled' => [
                'product_request_customer_status_id' => 'user-canceled',
                'product_request_supplier_status_id' => 'user-canceled',
                'product_request_delivery_status_id' => 'canceled',
            ],
        ],
    ],

    'product_request_supplier_status_id' => [
        'new' => [
            'supplier-refused' => [
                'product_request_customer_status_id' => 'supplier-refused',
                'product_request_supplier_status_id' => 'supplier-refused',
                'product_request_delivery_status_id' => 'canceled',
            ],
            'in-work' => [
                'product_request_customer_status_id' => 'in-work',
                'product_request_supplier_status_id' => 'in-work',
                'product_request_delivery_status_id' => 'waiting',
            ],
            'matching' => [
                'product_request_customer_status_id' => 'matching',
                'product_request_supplier_status_id' => 'matching',
            ],
        ],
        'on-the-way' => [
            'done' => [
                'product_request_customer_status_id' => 'on-the-way',
                'product_request_supplier_status_id' => 'done',
            ],
        ],
    ],

    'product_request_delivery_status_id' => [
        'waiting' => [
            'in-work' => [
                'product_request_supplier_status_id' => 'on-the-way',
                'product_request_delivery_status_id' => 'in-work',
            ],
        ],
    ],

];
